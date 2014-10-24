<?php

if (isset($_POST['imagizer-save-settings'])){
    
    $config->min->height=(int)(isset($_POST['min']['height'])?$_POST['min']['height']:$config->min->height);
    $config->min->width=(int)(isset($_POST['min']['width'])?$_POST['min']['width']:$config->min->width);
    $config->max->height=(int)(isset($_POST['max']['height'])?$_POST['max']['height']:$config->max->height);
    $config->max->width=(int)(isset($_POST['max']['width'])?$_POST['max']['width']:$config->max->width);
    $config->exact->height=(int)(isset($_POST['exact']['height'])?$_POST['exact']['height']:$config->exact->height);
    $config->exact->width=(int)(isset($_POST['exact']['width'])?$_POST['exact']['width']:$config->exact->width);
    
    $config->size=$_POST['size']=='var'?'var':'exact';
    $config->priority=$_POST['priority']=='min'?'min':'max';
    
    if ($config->min->height>$config->max->height){ swap($config->min->height,$config->max->height); }
    if ($config->min->width>$config->max->width){ swap($config->min->width,$config->max->width); }

    if (isset($_POST['convert-to-jpeg'])){
        $config->convert_to_jpeg=1;
        $config->watermark=(int)isset($_POST['place-watermark']);
        $config->watermark_text=isset($_POST['watermark-text'])?$_POST['watermark-text']:(string)$config->watermark_text;
        $config->compress=(int)isset($_POST['compress-to-size']);
        $config->compress_size=(int)(isset($_POST['compress-size'],$_POST['compress-size-exp'])?
                                        $_POST['compress-size']*$_POST['compress-size-exp']:
                                        $config->compress_size);
    }
    else {
        $config->convert_to_jpeg=0;
    }    
}


function swap(&$a,&$b){
    $c=$a;
    $a=$b;
    $b=$c;
}


?>