<?php
/*1.Сперва нужно создать приложение. Сделать это можно здесь http://vk.com/editapp?act=create. 
	Выбираете standalone-приложение. После создания вашему приложению присвоится ID. 
  2. Токен для работы приложения с api получить несложно, достаточно в браузере открыть ссылку:
	https://oauth.vk.com/authorize?client_id=[ID приложения]&scope=[запрашиваемые права]&display=page&response_type=token&redirect_uri=https://oauth.vk.com/blank.html
	ID приложения мы получили в предыдущем шаге.
	Для работы моего класса необходимо запросить следующие права: offline,group,photos,wall. 
	Весь список возможных прав можно найти в документации к api. 
	
	https://oauth.vk.com/authorize?client_id=4829407&scope=offline,group,photos,wall&display=page&response_type=token&redirect_uri=https://oauth.vk.com/blank.html
	https://oauth.vk.com/blank.html#access_token=fc0d2979994e4caa178db05c2793e5b3d57f26710beafe3d48303e5ad65114860440eb12c18c61face7ab&expires_in=0&user_id=2263119
*/

function randValue($length){
  //return substr(chr( mt_rand( 97 ,122 ) ) .substr( md5( time( ) ) ,1 ),0,15);
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString; 
}

	include_once 'vk.php';

	$token = 'fc0d2979994e4caa178db05c2793e5b3d57f26710beafe3d48303e5ad65114860440eb12c18c61face7ab';
	$delta = '100'; //вероятность того, что запись опубликуется на стену
	$app_id = '4829407';//ID приложения
	$group_id = '89743825';//ID группы

	/*
	 $vk = new vk( $token, $delta, $app_id, $group_id );	
	 //$vk_photo = $vk->upload_photo($my_img, $vk_album, 'Арт: '.$my_articl.'; Размер '.$my_text.'. Цена '. $my_price);
	 $vk_post = $vk->post('Размер '.$my_text.'. Цена '. $my_price, $vk_photo, '_' );
	*/

	$in_file='in.csv';
	$out_file='out.csv';
	$last_post_id[]='';

	if (!file_exists($out_file)){$fo = fopen ($out_file, 'w');fclose($fo);}
	if (file_exists($out_file)) $f_out = fopen($out_file,"r+");
	if (file_exists($in_file))  $f_in = fopen ($in_file,"r");
	while ($data = fgetcsv ($f_out, 10000, ";")) $last_post_id[] = $data[0];  

//$my_text= iconv("WINDOWS-1251","UTF-8", $data[2]);
//usleep(400000);//Делаем меньше запросов к vk

    /**/
	while($data = fgetcsv($f_in, 10000, ";")){
		 $my_id= $data[0];
		 if (!in_array($my_id, $last_post_id))
			{
			  
			  $my_txt= $data[1];
			  $vk = new vk( $token, $delta, $app_id, $group_id );
			  $vk_post = $vk->post( iconv("WINDOWS-1251","UTF-8",  $my_txt) , $vk_photo, '_' );
			  fwrite($f_out, $my_id."\r\n");
			  echo($j.';'.$my_id.';'.$data[1].'<br>');
			  break;
			}
	  }
	 /**/

	/*$inx_file='mmm.csv'; 
	$ino_file='mmmOut.csv'; 
	if (file_exists($inx_file)) $f_in = fopen($inx_file,"r");
	if (!file_exists($ino_file)){$fo = fopen ($ino_file, 'w');fclose($fo);}
	if (file_exists($ino_file)) $f_out = fopen($ino_file,"r+");
	
	while($data = fgetcsv($f_in, 10000, ";")){
			  $my_id=randValue(20);
			  $my_txt= $data[1]; 
			  fwrite($f_out, $my_id.';'.$my_txt."\r\n"); 
			  // echo( $my_id.';'.$my_txt.'<br>'); 
	  }*/ 