<?php
//author admin@cbf.in.ua 
//crontab runs 8-17
class ControllerModuleTr26lostcart extends Controller {
	public function index() {
		$array_day_proceed = array(0=>1,1=>2,2=>5);//24 hours, 2days, 5 days
		
		
		$today = date('Y-m-d H:i:s');
		$_time = date('H');
			
		foreach($array_day_proceed as $ind=>$day)
		{
			$req0='';$req24='';$req17='';
		
			if($_time==8)
			{
				$b0 = new DateTime($today);
				if($day==1)
				{
					$b0->modify("-8 hour");
				}
				else
				{
					$b0->modify( "-".$day -1 ." days -8 hour");
				}
				$req0=$b0->format('Y-m-d H:i:s');
			}
			else if($_time==17)
			{
				$b24 = new DateTime($today);
				$b24->modify("-". $day ." days +7 hour");
				$req24=$b24->format('Y-m-d H:i:s');
				
				$b17 = new DateTime($today);
				$b17->modify("-". $day ." days +1 hour");
				$req17=$b17->format('Y-m-d H:i:s');
			}
			
			
			
			
			$b3h = new DateTime($today);
			$b3h->modify("-".$day." days");
			$reqb3h=$b3h->format('Y-m-d H:i:s');	
					
			
			$_start = (int) $_time - 8;
			$bsh = new DateTime($reqb3h);
			$bsh->modify("-".$_start." hour");
			$st_chasov = $bsh->format('Y-m-d H:i:s');
			
			$b4h = new DateTime($today);
			$b4h->modify("-".$day." days +59 minutes +59 second");
			$reqb4h=$b4h->format('Y-m-d H:i:s');
			
			
			$ost_time = 17 - (int) $_time;
			$bh = new DateTime($reqb4h);
			$bh->modify("+".$ost_time." hour");
			$ost_chasov = $bh->format('Y-m-d H:i:s');
			
			
			//echo $ost_chasov; die;
			
			$this->load->model('module/lostcart');
			$this->load->model('module/lng');
			
			if($req0)
			{
				$this->sendform($today,$req0,$day,$ost_chasov="",$st_chasov="");
			}
			elseif($req24 && $req17)
			{
				$this->sendform($req17,$req24,$day,$ost_chasov="",$st_chasov="");
			}
			
			$this->sendform($reqb3h,$reqb4h,$day,$ost_chasov,$st_chasov);
		}
		
		
		
	}
	public function sendform($reqb3h,$reqb4h,$day,$ost_chasov,$st_chasov)
	{
		//echo 'in func= '.$reqb3h.' '.$reqb4h.' '.$day.' '.$ost_chasov.' '.$st_chasov.' '. date('H');//die;
		$this->load->model('module/lostcart');
		$this->load->model('module/lng');
		$lostcarts = $this->model_module_lostcart->getRecordsTreeHour($reqb3h,$reqb4h);
		//var_dump($lostcarts);
		if($lostcarts)
		{
			if ($this->request->server['HTTPS'])
			{
				$server = $this->config->get('config_ssl');
			} 
			else
			{
				$server = $this->config->get('config_url');
			}
			$data['logo'] = $server . 'image/email/';
			$option_data = array(); 
			
			
			foreach($lostcarts as $customer)
			{
				
				if($customer['email']!='')
				{
						$isAnyCart = $this->model_module_lostcart->getRecordsIdAndHour($customer['customer_id'],$reqb4h,$ost_chasov);
						//var_dump($isAnyCart);
							/*echo '<pre>';
							print_r($isAnyCart);
							echo '</pre>';
							*/
							
						if($isAnyCart)
						{
							$LostCartNew = $this->model_module_lostcart->getRecordsIdAndHour($customer['customer_id'],$st_chasov,$ost_chasov);
							
							if($LostCartNew)
							{
								
								$time_zap = explode(' ',$LostCartNew[0]['date_added']);
								if($time_zap)
								{	
									$mas_time_zap = explode(':',$time_zap[1]);
									if($mas_time_zap && $mas_time_zap[0]==date('H'))
									{	
										foreach($LostCartNew as $customerNew)
										{
											$lng = ($customerNew['zone_id'])?$this->model_module_lng->getEmaillng($customerNew['zone_id']):2;
											$option_data[$customerNew['email']][]=array(
												'customer_id' => $customerNew['customer_id'],
												'product_id' => $customerNew['product_id'],
												'date_added' => $customerNew['date_added'],
												'quantity' => $customerNew['quantity'],
												'firstname' => $customerNew['firstname'],
												'lastname' => $customerNew['lastname'],
												'email' => $customerNew['email'],
												'zone_id' => $customerNew['zone_id'],
												'lng' => $lng
												
											);
											if($customerNew['product_id'] && $lng)
											{
												$prod_data = $this->model_module_lostcart->getProducts($customerNew['product_id'],$lng);
												if($prod_data)
												{
													$mas_prod_data[$customerNew['product_id']] = $prod_data; 
														
												};
													
											};
										}
									
									}
								}
								
							}
						}
						else
						{
							$lng = ($customer['zone_id'])?$this->model_module_lng->getEmaillng($customer['zone_id']):2;
							$option_data[$customer['email']][]=array(
								'customer_id' => $customer['customer_id'],
								'product_id' => $customer['product_id'],
								'date_added' => $customer['date_added'],
								'quantity' => $customer['quantity'],
								'firstname' => $customer['firstname'],
								'lastname' => $customer['lastname'],
								'email' => $customer['email'],
								'zone_id' => $customer['zone_id'],
								'lng' => $lng
								
							);
							if($customer['product_id'] && $lng)
							{
								$prod_data = $this->model_module_lostcart->getProducts($customer['product_id'],$lng);
								if($prod_data)
								{
									$mas_prod_data[$customer['product_id']] = $prod_data; 
										
								};
									
							};
						}
				}
			}
			
			
			if($option_data)
			{
				
			
				
				foreach($option_data as $key=>$value)
				{
					$rows_product='';
					$data['str']='';
					$mail = new Mail();
					$mail->protocol = $this->config->get('config_mail_protocol');
					$mail->parameter = $this->config->get('config_mail_parameter');
					$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
					$mail->smtp_username = $this->config->get('config_mail_smtp_username');
					$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
					$mail->smtp_port = $this->config->get('config_mail_smtp_port');
					$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
					$cnt = 	count($value);$j=1;
					foreach($value as $k=>$val)
					{
						
						//print_r($value);die;
						if($mas_prod_data[$val['product_id']][0]['product_id'])
						{
						$lang=($val['lng']==2)?'ru':'uk';
						$imgpath= $server . 'image/email/trigger/19/version1/'. $lang .'/';
						
						$path_gif = $server . 'image/email/1.gif';
						$rows_product.='<tr>';
							$rows_product.='<td width="600" height="140" valign="middle" colspan="2" >';
								$rows_product.='<table class="pad_null" width="600" align="left" border="0" cellspacing="0" cellpadding="0">';
									$rows_product.='<tr>';
										$rows_product.='<td width="81" height="140" bgcolor="#ffffff"><img style="display:block;" border="0" src="'.$imgpath.'trig19_img3.jpg" alt="" width="81" height="140" /></td>';
										$rows_product.='<td width="140" height="140" align="left" bgcolor="#ffffff" valign="middle">
											<a style="text-decoration:none;font-weight: bold;" href="'.$server.'index.php?route=product/product&product_id='.(int) $val['product_id'].'" title=""><img style="display:block;font-size: 13px; font-weight: bold; color: #1751bb; background-color: #ffffff; text-align: center; font-family:verdana; valign: middle;" border="0" src="'.$server.'image/'.$mas_prod_data[$val['product_id']][0]['image'].'" alt="'.$mas_prod_data[$val['product_id']][0]['name'].'" width="140" height="140" /></a>
										</td>';
										$rows_product.='<td width="30" bgcolor="#ffffff"><img style="display:block;" border="0" src="'.$path_gif.'" alt="" width="30" /></td>';
										$rows_product.='<td width="274" height="140" align="left" bgcolor="#ffffff" valign="top">';
											$rows_product.='<table class="pad_null" width="274" align="left" border="0" cellspacing="0" cellpadding="0">';
												$rows_product.='<tr>
													<td width="274" height="60" align="left" bgcolor="#ffffff">
														<div style="line-height:18px;font-size:15px;text-align:left;" align="left">
															<font face="Tahoma, sans-serif" color="#373737">
																'.$mas_prod_data[$val['product_id']][0]['name'].'
															</font>
														</div>
													</td>
												</tr>
												<tr>';
													$rows_product.='<td width="274" height="" valign="middle" colspan="2" >';
														$rows_product.='<table class="pad_null" width="274" align="left" border="0" cellspacing="0" cellpadding="0">';
															$rows_product.='<tr>
																<td width="90" height="30" align="left" bgcolor="#ffffff">
																	<div style="line-height:18px;font-size:15px;text-align:left;" align="left">
																		<font face="Tahoma, sans-serif" color="#373737">
																			'.sprintf("%.2f",$mas_prod_data[$val['product_id']][0]['price_roz']).'
																		</font>
																	</div>
																</td>
																<td width="60" height="30" align="left" bgcolor="#ffffff">
																	<div style="line-height:20px;font-size:18px;text-align:left;" align="left">
																		<font face="Tahoma, sans-serif" color="#3a8a3a">
																			<b>'.$mas_prod_data[$val['product_id']][0]['discount_procent'].'%</b>
																		</font>
																	</div>
																</td>
																<td width="124" height="30" align="left" bgcolor="#ffffff">
																	<div style="line-height:20px;font-size:18px;text-align:left;" align="left">
																		<font face="Tahoma, sans-serif" color="#373737">
																			'.sprintf("%.2f",$mas_prod_data[$val['product_id']][0]['price']).'
																		</font>
																	</div>
																</td>
															</tr>';	
														$rows_product.='</table>';	
													$rows_product.='</td>';	
												$rows_product.='</tr>';
												$rows_product.='<tr>
													<td width="274" height="" align="left" bgcolor="#ffffff">
														<div style="line-height:18px;font-size:14px;text-align:left;" align="left">
															<font face="Tahoma, sans-serif" color="#707070">
																<b>'.$val['quantity'].' шт.</b>
															</font>
														</div>
													</td>';
												$rows_product.='</tr>';
											$rows_product.='</table>';
										$rows_product.='</td>';
										$rows_product.='<td width="75" height="140" bgcolor="#ffffff"><img style="display:block;" border="0" src="'.$imgpath.'trig19_img4.jpg" alt="" width="75" height="140" /></td>';
									$rows_product.='</tr>';
								$rows_product.='</table>';
							$rows_product.='</td>';
						$rows_product.='</tr>';
							
							if($j<$cnt)
							{
								$rows_product.='<tr>
										<td  width="600" height="29" valign="top" bgcolor="#ffffff" style="background-color:#ffffff"><img style="display:block;" border="0" src="'.$imgpath.'trig19_img5.jpg" alt="" width="600" height="29" /></td>
									</tr>';
							}
							$j++;
						}
						
						
					}
					//echo $rows_product;die;
					
					if($day==1)
					{
						//24 hour
						$lang=($val['lng']==2)?'ru':'uk';
						//$shablon=($val['lng']==2)?'tr26_lostcart':'tr26_lostcart';
						$shablon='tr26_lostcart';
						$data['heading_thaks']=($val['lng']==2)?'Незавершенный заказ':'Незакінчене замовлення';
						$data['heading_title_more'] = ($val['lng']==2)?'Успейте купить, пока есть в продаже':'Встигніть придбати, доки є у продажу';
						$subject=($val['lng']==2)?'Ваши товары уже в «Корзине». Осталось заказать!':'Ваші товари вже у «Кошику». Залишилося лише замовити!';
						$data['heading_title']=($val['lng']==2)?'Незавершенный заказ. Успейте купить, пока есть в продаже':'Незакінчене замовлення. Встигніть придбати, доки є у продажу';
						$data['txt_zvern']=($val['lng']==2)?$val['firstname'].', мы обратили внимание, что вчера Вы добавили в свою «Корзину» косметические средства, но так и не заказали их.':'Шановний клієнте, ми звернули увагу, що вчора Ви додали до свого «Кошика» косметичні засоби, проте так і не замовили їх.';
						$data['txt_zakon_oform']=($val['lng']==2)?'Предлагаем Вам закончить оформление заказа прямо сейчас, пока все товары есть в наличии. Для этого просто зайдите в Вашу «<a href="'.$server.'index.php?route=checkout/cart" style="text-decoration:underline; color:#227e22;" title="Корзину"><font face="Tahoma, Arial, sans-serif" color="#227e22"><b>Корзину</b></font></a>».':'Пропонуємо Вам закінчити оформлення замовлення просто зараз, доки всі товари є у продажуі. Для цього просто зайдіть до Вашого «<a href="'.$server.'index.php?route=checkout/cart" style="text-decoration:underline; color:#227e22;" title="Кошика"><font face="Tahoma, Arial, sans-serif" color="#227e22"><b>Кошика</b></font></a>».';
						$metrika = 'utm_source=email_trigger-26&utm_medium=email&utm_campaign=email_trigger-26-abandoned_cart_bottegaverde-ua_lng-'.$lang.'&utm_content=email_trigger-26_date-'.date('Ymd').'_count-1';
					}
					elseif($day==2)
					{
						//2 days
						$lang=($val['lng']==2)?'ru':'uk';
						$shablon='tr26_lostcart';
						$subject=($val['lng']==2)?'Шаблон брошенной корзины на русском языке(2 дня)':'Шаблон покинутой корзини на українській мові(2 дні)';	
						$data['heading_title']=($val['lng']==2)?'Тайтл письма брошенной корзины на русском языке(2 дня)':'Тайтл листа покинутой корзини на українській мові(2 дні)';
						$metrika = 'utm_source=email_trigger-27&utm_medium=email&utm_campaign=email_trigger-27-abandoned_cart_bottegaverde-ua_lng-'.$lang.'&utm_content=email_trigger-27_date-'.date('Ymd').'_count-1';
					}
					else
					{
						//$5 hour
						$lang=($val['lng']==2)?'ru':'uk';
						$shablon='tr26_lostcart';
						$subject=($val['lng']==2)?'Шаблон брошенной корзины на русском языке(5 дней)':'Шаблон покинутой корзини на українській мові(5 днів)';	
						$data['heading_title']=($val['lng']==2)?'Тайтл письма брошенной корзины на русском языке(5 дней)':'Тайтл листа покинутой корзини на українській мові(5 днів)';
						$metrika = 'utm_source=email_trigger-28&utm_medium=email&utm_campaign=email_trigger-28-abandoned_cart_bottegaverde-ua_lng-'.$lang.'&utm_content=email_trigger-28_date-'.date('Ymd').'_count-1';
					}
					
					$language_code = ($val['lng']==2)?'ru-ru':'ua-uk';		
					$language = new Language($language_code);
					$language->load($language_code);
					$language->load('mail/footer');
				
					
					$data['need_help']=$language->get('need_help');
					$data['footerlink'] =array(
								1=>sprintf($language->get('link_1'), $this->url->link('information/information', 'information_id=14&'.$metrika, true)),
								2=>sprintf($language->get('link_2'), $this->url->link('information/information', 'information_id=6&'.$metrika, true)),
								3=>sprintf($language->get('link_3'), $this->url->link('account/account', '?'.$metrika, true)),
								4=>sprintf($language->get('link_4'), $this->url->link('information/information', 'information_id=17&'.$metrika, true)),
								5=>sprintf($language->get('link_5'), $this->url->link('information/information', 'information_id=4&'.$metrika, true))
								);
					$data['is_gues']=$language->get('is_gues');
					$data['besp_line']=$language->get('besp_line');
					$data['phone_num']=$language->get('phone_num');
					$data['email_sup']=$language->get('email_sup');
					$data['unsibscr']=sprintf($language->get('unsibscr'), $this->url->link('module/ocmnews/unsubscribe('.$this->encryption->encrypt($key).')', '', true));	
					//end footer	
					
					
					
					$data['footer_image_width'] =($val['lng']==2)?array(97,94,95,103,86,125):array(93,114,88,113,75,117);
					$data['vernumber'] = 1;
					$data['store_url'] = $server;
					$data['metrika'] = $metrika;
					$data['path_gif'] = $server . 'image/email/1.gif';
					$data['img_path'] = $server . 'image/email/trigger/';
					$data['store_name'] = $this->config->get('config_name');
					$data['lng']= ($val['lng']==2)?'ru':'uk';
					$data['footer'] = $this->load->view('mail/footer', $data); 	
					$data['str'] =  $rows_product;
					//echo $data['str'];
					$mail->setTo($key);
					$mail->setFrom($this->config->get('config_email'));
					$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
					$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
					$mail->setHtml($this->load->view('mail/'.$shablon, $data));
					$mail->send();
					
				}
			}	
		
			
		}
	}
}