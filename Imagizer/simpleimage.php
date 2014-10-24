<?php

//watermark colors:
define('WATERMARK_RED',0);
define('WATERMARK_GREEN',0);
define('WATERMARK_BLUE',0);
define('WATERMARK_OPACITY',90);
//watermark font path:
define('WATERMARK_FONT',GSPLUGINPATH.'Imagizer/font.ttf');
 
class Image {
 
    private static $_image=0;
    private static $_image_type=null;
    private static $_height=null;
    private static $_width=null;
    private static $_filename=null;
    
    private function __construct(){}
    private function __clone(){}
    
    public static function load($filename) {
        if (!file_exists($filename)) return false;
        
        $image_info = getimagesize($filename);
        
        self::$_image_type=$image_info[2];
        self::$_width=$image_info[0];
        self::$_height=$image_info[1];
        self::$_filename=$filename;       
        
        if( self::$_image_type == IMAGETYPE_JPEG ) {
        
            self::$_image = imagecreatefromjpeg($filename);
        } elseif( self::$_image_type == IMAGETYPE_GIF ) {
        
            self::$_image = imagecreatefromgif($filename);
        } elseif( self::$_image_type == IMAGETYPE_PNG ) {
        
            self::$_image = imagecreatefrompng($filename);
        } elseif( self::$_image_type == IMAGETYPE_BMP ) {
        
            self::imagecreatefrombmp($filename);
        }
        else {
            self::$_image=null;
            debugLog('Invalid image format');
            return false;
        }
        return true;
    }
    public static function save($filename='', $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null) {
        if (empty($filename)) $filename=self::$_filename;
        
        if( $image_type == IMAGETYPE_JPEG ) {
            if ($image_type!=self::getType()){
                unlink($filename);
                self::changeExt('jpg',$filename);    
            }
            imagejpeg(self::$_image,$filename,$compression);
        } elseif( $image_type == IMAGETYPE_GIF ) {
        
            imagegif(self::$_image,$filename);
        } elseif( $image_type == IMAGETYPE_PNG ) {
        
            imagepng(self::$_image,$filename);
        } elseif( $image_type == IMAGETYPE_BMP ) {
        
            self::imagebmp($filename);
        }
        else {
            return false;
        }
        if(!empty($permissions)) {
        
            chmod($filename,$permissions);
        }
        return self::load($filename);
    }
    
    public static function getWidth() {
        return self::$_width;
    }
    public static function getHeight() {
        return self::$_height;
    }
    
    public static function getType(){
        return self::$_image_type; 
    }
    
    public static function getFilename(){
        return self::$_filename;
    }
    
    public static function resizeToHeight($height) {
        $ratio = $height / self::getHeight();
        $width = self::getWidth() * $ratio;
        self::resize($width,$height);
    }
    
    public static function resizeToWidth($width) {
        $ratio = $width / self::getWidth();
        $height = self::getHeight() * $ratio;
        self::resize($width,$height);
    }
    
    public static function scale($scale) {
        $width = self::getWidth() * $scale/100;
        $height = self::getHeight() * $scale/100;
        self::resize($width,$height);
    }
    
    public static function resize($width,$height) {
        $new_image = imagecreatetruecolor($width, $height);
        imagecopyresampled($new_image, self::$_image, 0, 0, 0, 0, $width, $height, self::getWidth(), self::getHeight());
        self::$_image = $new_image;
        self::$_height=imagesy(self::$_image);
        self::$_width=imagesx(self::$_image);
    }     
    
    public static function cropCenter($w,$h){
        $new_image = imagecreatetruecolor($w, $h);   
        
        $delta_w=abs($w-self::getWidth());
        $delta_h=abs($h-self::getHeight());
           
        if ($delta_w<$delta_h) {
            self::resizeToWidth($w);  
            imagecopy($new_image,self::$_image,0,0,0,round((self::getHeight()-$h)/2),$w,$h);  
        }
        else {
            self::resizeToHeight($h);
            imagecopy($new_image,self::$_image,0,0,round((self::getWidth()-$w)/2),0,$w,$h);
        }
        self::$_image = $new_image;
        self::$_height=imagesy(self::$_image);
        self::$_width=imagesx(self::$_image);
    }
    
    public static function fitMin($min_w,$min_h){
        $delta_w=$min_w-self::getWidth();
        $delta_h=$min_h-self::getHeight();
        
        if ($delta_h>0 || $delta_w>0){
            if ($delta_h>$delta_w) self::resizeToHeight($min_h);
            else self::resizeToWidth($min_w);
        }
    } 
    
    public static function fitMax($max_w,$max_h){
        $delta_w=self::getWidth()-$max_w;
        $delta_h=self::getHeight()-$max_h;
        
        if ($delta_h>0 || $delta_w>0){
            if ($delta_h>$delta_w) self::resizeToHeight($max_h);
            else self::resizeToWidth($max_w);
        }
    }
    
    public static function applyWatermark($text){
        
        $angle =  -rad2deg(atan2( (-self::getHeight()) , self::getWidth() ));
        $text = " ".$text." ";
        $color = imagecolorallocatealpha(self::$_image, WATERMARK_RED, WATERMARK_GREEN, WATERMARK_BLUE, WATERMARK_OPACITY);
        $size = ((self::getWidth()+self::getHeight())/2)*2/strlen($text);
        $box  = imagettfbbox ( $size, $angle, WATERMARK_FONT, $text );
        $x = self::getWidth()/2 - abs($box[4] - $box[0])/2;
        $y = self::getHeight()/2 + abs($box[5] - $box[1])/2;
        
        imagettftext(self::$_image,$size,$angle, $x, $y, $color, WATERMARK_FONT, $text);

    }
    
    private static function imagecreatefrombmp($filename){ 
        $file = fopen($filename,"rb"); 
        $read = fread($file,10); 
        while(!feof($file)&&($read<>"")) $read.=fread($file,1024); 
        $temp = unpack("H*",$read); 
        $hex = $temp[1]; 
        $header = substr($hex,0,108); 
        if (substr($header,0,4)=="424d") 
        { 
            $header_parts=str_split($header,2); 
            $width=hexdec($header_parts[19].$header_parts[18]); 
            $height=hexdec($header_parts[23].$header_parts[22]); 
            unset($header_parts); 
        } 
        $x=0; 
        $y=1; 
        $image=imagecreatetruecolor($width,$height); 
        $body=substr($hex,108); 
        $body_size=(strlen($body)/2); 
        $header_size=($width*$height);  
        $usePadding=($body_size>($header_size*3)+4); 
        $old_r=0;
        $old_b=0;
        $old_g=0;
        for ($i=0;$i<$body_size;$i+=3) 
        { 
            if ($x>=$width) 
            { 
                if ($usePadding) $i+=$width%4; 
                $x=0; 
                $y++; 
                if ($y>$height) break; 
            } 
            $i_pos=$i*2; 
            
            // need speed improvement, each hexdec costs 250 ms
            //$r=hexdec($body[$i_pos+4].$body[$i_pos+5]); 
            //$g=hexdec($body[$i_pos+2].$body[$i_pos+3]); 
            //$b=hexdec($body[$i_pos].$body[$i_pos+1]);
            //hexdec was optimized, 3 times speed win
            // the new revolutional way to convert numbers from dec to hex =)
            $r='0x'.$body[$i_pos+4].$body[$i_pos+5]; 
            $g='0x'.$body[$i_pos+2].$body[$i_pos+3];
            $b='0x'.$body[$i_pos].$body[$i_pos+1];
            if (!($r==$old_r && $g==$old_g && $b==$old_b)){
               $old_r=$r;
               $old_g=$g;
               $old_b=$b; 
               $color=imagecolorallocate($image,$r,$g,$b);
            }
            imagesetpixel($image,$x,$height-$y,$color); 
            $x++; 
        } 
        self::$_image=$image;
        unset($image);
        unset($body); 
    }
    
    private static function imagebmp($filename){
      $widthFloor = ((floor(self::getWidth()/16))*16);
      $widthCeil = ((ceil(self::getWidth()/16))*16);
      $img_width=self::getWidth();
      $height=self::getHeight();
      $size = ($widthCeil*$height*3)+54;
      $result = 'BM';     
      $result .= self::int_to_dword($size); // size of file (4b)
      $result .= self::int_to_dword(0); // reserved (4b)
      $result .= self::int_to_dword(54);  // byte location in the file which is first byte of IMAGE (4b)
      // Bitmap Info Header
      $result .= self::int_to_dword(40);  // Size of BITMAPINFOHEADER (4b)
      $result .= self::int_to_dword($widthCeil);  // width of bitmap (4b)
      $result .= self::int_to_dword($height); // height of bitmap (4b)
      $result .= self::int_to_word(1);  // biPlanes = 1 (2b)
      $result .= self::int_to_word(24); // biBitCount = {1 (mono) or 4 (16 clr ) or 8 (256 clr) or 24 (16 Mil)} (2b)
      $result .= self::int_to_dword(0); // RLE COMPRESSION (4b)
      $result .= self::int_to_dword(0); // width x height (4b)
      $result .= self::int_to_dword(0); // biXPelsPerMeter (4b)
      $result .= self::int_to_dword(0); // biYPelsPerMeter (4b)
      $result .= self::int_to_dword(0); // Number of palettes used (4b)
      $result .= self::int_to_dword(0); // Number of important colour (4b) 
      // is faster than chr()
      $arrChr = array();
      for($i=0; $i<256; $i++){
        $arrChr[$i] = chr($i);
      }
      // creates image data
      $bgfillcolor = array("red"=>0, "green"=>0, "blue"=>0);
      // bottom to top - left to right - attention blue green red !!!
      $y=$height-1;
      for ($y2=0; $y2<$height; $y2++) {
        for ($x=0; $x<$widthFloor;  ) {
          $rgb=imagecolorat(self::$_image, $x++, $y);
          $result.=$arrChr[$rgb & 0xFF].$arrChr[($rgb >> 8) & 0xFF].$arrChr[($rgb >> 16) & 0xFF];
        }
        for ($x=$widthFloor; $x<$widthCeil; $x++) {
          $rgb = ($x<$img_width) ? imagecolorsforindex(self::$_image, imagecolorat(self::$_image, $x, $y)) : $bgfillcolor;
          $result .= $arrChr[$rgb["blue"]].$arrChr[$rgb["green"]].$arrChr[$rgb["red"]];
        }
        $y--;
      }
      
        $file = fopen($filename, "wb");
        fwrite($file, $result);
        fclose($file);

    }
    
    // imagebmp helpers
    private static function int_to_dword($n){
      return chr($n & 255).chr(($n >> 8) & 255).chr(($n >> 16) & 255).chr(($n >> 24) & 255);
    }
    private static function int_to_word($n){
      return chr($n & 255).chr(($n >> 8) & 255);
    }
    
    
    private static function changeExt($new_ext,&$filename){
        $filename=substr_replace($filename,$new_ext,strrpos($filename,'.')+1);
    }
    
}
?>