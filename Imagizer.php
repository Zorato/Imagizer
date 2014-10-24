<?php

i18n_merge('Imagizer') || i18n_merge('Imagizer','en_US');

# get correct id for plugin
$thisfile = basename(__FILE__, ".php");

# register plugin
register_plugin(
	$thisfile, //Plugin id
	'Imagizer', 	//Plugin name
	'0.2', 		//Plugin version
	'Alexey Rehov',  //Plugin author (nickname: Zorato)
	'http://github.com/Zorato/Imagizer', //author website
	'Fully automatical after-upload image handler', //Plugin description
	'files', //page type - on which admin tab to display
	'imagizer_settings'  //main function (administration)
);

//TODO: customizable watermark

add_action('files-sidebar','createSideMenu',array($thisfile,'Imagizer'));
add_action('file-uploaded','imagizer_handle');


function imagizer_handle(){ 
    $config=load_config();
    $file=array();
    $file=isset($_FILES['file'])?$_FILES['file']:(isset($_FILES['Filedata'])?$_FILES['Filedata']:false);
    if ($file===false) return;

    $file['type']=is_array($file['type'])?$file['type'][0]:$file['type'];
    $file['name']=is_array($file['name'])?$file['name'][0]:$file['name'];
    $file['ext'] = lowercase(pathinfo($file['name'],PATHINFO_EXTENSION));
    
    if (!defined('GSNOUPLOADIFY')) {
        $file['target'] = str_replace('//','/',(isset($_POST['path'])) ? GSDATAUPLOADPATH.$_POST['path']."/" : GSDATAUPLOADPATH);
    }
    else {
        $file['target'] = isset($_GET['path'])?tsl("../data/uploads/".str_replace('../','', $_GET['path'])):"../data/uploads/";
    }
    $file['target'].= clean_img_name(to7bit($file['name']));
    
    $image_types=array('image/jpeg','image/pjpeg','image/png','image/gif','image/bmp');
    $image_ext=array('jpg','jpeg','png','gif','bmp');
 
    if (in_array($file['type'],$image_types) || in_array($file['ext'],$image_ext) ){ 
        
        include_once GSPLUGINPATH.'Imagizer/simpleimage.php';
        
        if (Image::load($file['target'])===false)  return; 
        
        if ($config->size=='var'){
            if ($config->priority=='min'){ //check max first, then min
                Image::fitMax((int)$config->max->width,(int)$config->max->height);
                Image::fitMin((int)$config->min->width,(int)$config->min->height);
            }
            else { //check min first, then max
                Image::fitMin((int)$config->min->width,(int)$config->min->height);
                Image::fitMax((int)$config->max->width,(int)$config->max->height);
            }
        }
        else { //exact size
            Image::cropCenter((int)$config->exact->width,(int)$config->exact->height);    
        }
        
        if ((int)$config->convert_to_jpeg===1){
            if ((int)$config->watermark===1 && $config->watermark_text!=''){
                
                Image::applyWatermark($config->watermark_text);
                
            }
            Image::save();
            if ((int)$config->compress===1 && (int)$config->compress_size>0){
                $max_size=(int)$config->compress_size;
                $filesize=filesize($file['target']);
                if ($filesize>$max_size){    
                    $diff=floor($max_size/$filesize*100);                    
                    $compression=floor(0.75*$diff);
                    Image::save($file['target'],IMAGETYPE_JPEG,$compression);
                    clearstatcache();
                    $filesize=filesize($file['target']);
                }
            }
        }
        else { 
            Image::save($file['target'],Image::getType());
        } 

    }
}

function imagizer_settings(){
    $config=load_config();
    include GSPLUGINPATH.'Imagizer/settings_handler.php';
    save_config($config);
    include GSPLUGINPATH.'Imagizer/settings_viewer.php';
}

function load_config(){
    $c=getXML(GSPLUGINPATH.'Imagizer/config.xml');
    if (!$c){ //default configuration
        $c->ratio='save';
        $c->size='exact';
        $c->priority='max';
        $c->max->height = 1000;
        $c->max->width  = 1000;
        $c->min->height = 0;
        $c->min->width  = 0;
        $c->exact->height=300;
        $c->exact->width =300;
        $c->convert_to_jpeg=0;
        $c->watermark=0;
        $c->watermark_text='';
        $c->compress=0;
        $c->compress_size=0;
        save_config($c);
    }
    return $c;
}

function save_config(SimpleXMLExtended $c){
    XMLsave($c,GSPLUGINPATH.'Imagizer/config.xml');
}