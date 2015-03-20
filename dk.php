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
include_once 'lib/vk.php';
include_once 'lib/dBug.php';//Работа с переменными  new dBug($data);
include_once 'lib/image.php';

Class auto_posting {

    private $token;
    private $delta; //вероятность того, что запись опубликуется на стену
    private $app_id;//ID приложения
    private $group_id;//ID группы
    private $in_file;
    private $out_file;
    private $last_post_id;
	private $last_img_id;

    function __construct($fileName)
      {

        $this->token = 'fc0d2979994e4caa178db05c2793e5b3d57f26710beafe3d48303e5ad65114860440eb12c18c61face7ab';
        $this->delta = '100'; //вероятность того, что запись опубликуется на стену
        $this->app_id = '4829407';//ID приложения
        $this->group_id = '89743825';//ID группы
        $this->albom_id = '212958817';//ID альбома
        $this->in_file = $fileName;
        $this->out_file = 'out.csv';
        $this->fileprepare();//Готовим файлы чтения записи

      }

      private function fileprepare(){
        if (!file_exists($this->out_file)){$fo = fopen ($this->out_file, 'w');fclose($fo);}
        if (file_exists($this->out_file)) $this->f_out = fopen($this->out_file,"r+");
        if (file_exists($this->in_file))  $this->f_in = fopen ($this->in_file,"r");
	    while ($data = fgetcsv ($this->f_out, 10000, ";")) {$this->last_post_id[] = $data[0]; $this->last_img_id[] = $data[1]; } 
      }

      private function randValue($length){
        //return substr(chr( mt_rand( 97 ,122 ) ) .substr( md5( time( ) ) ,1 ),0,15);
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
          $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
      }

	  private function get_img_name(){
		  //получаем картинку,которая еще не загружалась
			    $dir = new DirectoryIterator('./img/');
				foreach($dir as $file){
				  if($file->isFile()){
					$filename=$file->getBasename(); 
				    if(!in_array($filename, $this->last_img_id)) {//еще не брали картинку
						$resizeObj = new resize('./img/'.$filename); //обработка
						$resizeObj -> resizeImage(800, 600, 'crop');
						$resizeObj -> saveImage("./img/".$filename, 100);
						return $filename;
						}
				  }
				}
      }
	  
      public function sourse_csv_create($in_file,$out_file,$get_filed_id){
            //создание файла данных (файл_источник, выходной_файл, номер_поля_из_источника_с_данными)
          if (file_exists($in_file)) $x_in = fopen($in_file,"r");
          if (!file_exists($out_file)){$xo = fopen ($out_file, 'w');fclose($xo);}
          if (file_exists($out_file)) $x_out = fopen($out_file,"r+");
          while($xdata = fgetcsv($x_in, 10000, ";")){
            $my_id=$this->randValue(20);
            $my_txt= $xdata[$get_filed_id];
            fwrite($x_out, $my_id.';'.$my_txt."\r\n");
          }
      }

      public function post(){
        while($data = fgetcsv($this->f_in, 10000,';')){
           $my_id= $data[0];
           if (!in_array($my_id, $this->last_post_id))
            {//берем первую запись, которая ранее не публиковалась
             $my_txt = substr($data[1],1,strlen($data[1]));
             $my_img=$this->get_img_name();
             $vk = new vk( $this->token, $this->delta, $this->app_id, $this->group_id );
             $vk_photo = $vk->upload_photo('./img/'.$my_img, $this->albom_id,  iconv("WINDOWS-1251","UTF-8",  $my_txt));
             $vk_post = $vk->post( iconv("WINDOWS-1251","UTF-8",  $my_txt) , $vk_photo, '_');
             fwrite($this->f_out, $my_id.';'.$my_img."\r\n");
             echo($my_id.';'.$my_txt.'<br>');
            break;
            }
           }
      }

}

$r = new auto_posting('in.csv');
$r->post();
//new dBug($r->last_img_id);
return 1;
//$r->sourse_csv_create('in.csv','xxx.csv',1);
 