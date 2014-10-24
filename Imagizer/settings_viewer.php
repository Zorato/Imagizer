<form method="POST">
    
    <fieldset>
    <legend style="margin-left:10px ;"><label><h3> <input type="radio" name="size" value="var" onclick="disable_group('exact')"<?php echo $config->size=='var'?'checked':''; ?> > <?php i18n('Imagizer/VAR_SIZE'); ?></h3></label></legend>
    
    <div style="float:left; margin-left:10px;">
        <h2><?php i18n('Imagizer/MIN_SIZE');?>:</h2>
        <label><?php i18n('Imagizer/WIDTH');?>:  <input type="text" name="min[width]"  class="text var"  style="width:120px;" value="<?php echo $config->min->width;?>"></label>
        <label style="margin-top: 2px!important;"><?php i18n('Imagizer/HEIGHT');?>: <input type="text" name="min[height]" class="text var"  style="width:120px;" value="<?php echo $config->min->height;?>"></label><br>
    </div>
    <div style="float: left; margin-left:50px;">
        <h2><?php i18n('Imagizer/MAX_SIZE');?>:</h2>
        <label><?php i18n('Imagizer/WIDTH');?>:  <input type="text" name="max[width]"  class="text var" style="width:120px;" value="<?php echo $config->max->width;?>"></label>
        <label style="margin-top: 2px!important;"><?php i18n('Imagizer/HEIGHT');?>: <input type="text" name="max[height]" class="text var" style="width:120px;" value="<?php echo $config->max->height;?>"></label><br>
    </div>
    <div style="float: left; margin-left:50px;">
        <h2><?php i18n('Imagizer/PRIORITY');?>:</h2>
        <label><input type="radio" name="priority" value="max" class="var" <?php echo $config->priority=='max'?'checked':''; ?> > <?php i18n('Imagizer/MAX');?> </label>
        <label><input type="radio" name="priority" value="min" class="var" <?php echo $config->priority=='min'?'checked':''; ?> > <?php i18n('Imagizer/MIN');?> </label>
    </div>
    <div style="clear: both;"></div>
    </fieldset>
    
    <fieldset>
    <legend style="margin-left:10px ;"><label><h3> <input type="radio"  name="size" value="exact" onclick="disable_group('var')" <?php echo $config->size=='exact'?'checked':''; ?> > <?php i18n('Imagizer/EXACT_SIZE');?> </h3></label></legend>
    <div style="float:left; margin-left:10px;">
        <h2>Size:</h2>
        <label><?php i18n('Imagizer/WIDTH');?>:  <input type="text" name="exact[width]"  class="text exact" style="width:120px;" value="<?php echo $config->exact->width;?>"></label>
        <label style="margin-top: 2px!important;"><?php i18n('Imagizer/HEIGHT');?>: <input type="text" name="exact[height]" class="text exact" style="width:120px;" value="<?php echo $config->exact->height;?>"></label><br>
    </div>
    <div style="clear: both;"></div>
    </fieldset>
    <br>
    
    
    <fieldset style="padding: 5px 9px !important;" id="additional">
    <legend style="margin-left:10px ;"><label><h3><input type="checkbox" name="convert-to-jpeg" id="to-jpeg" <?php echo (int)$config->convert_to_jpeg?'checked':''; ?> > <?php i18n('Imagizer/CONVERT');?></h3></label></legend>
    <label style="margin:3px;"><input type="checkbox" name="place-watermark" class="to-jpeg watermark" <?php echo (int)$config->watermark?'checked':'';?> > <?php i18n('Imagizer/WATERMARK');?>:</label>
    <input type="text" name="watermark-text" class="to-jpeg watermark text" style="width:250px;" value="<?php  echo (string)$config->watermark_text;?>">
    <label style="margin:3px;"><input type="checkbox" name="compress-to-size" class="to-jpeg compress" <?php echo (int)$config->compress?'checked':''; ?> > <?php i18n('Imagizer/COMPRESS');?>:</label>
    <?php 
        $size=(int)$config->compress_size;
        $exp=0; 
        while($size>1000) { 
            $size/=1000;
            $exp++; 
            
        }
    ?>
    <input type="text" name="compress-size" class="to-jpeg compress text" style="width:50px;" value="<?php echo $size; ?>">
    <select name="compress-size-exp" class="to-jpeg compress text" style="width:70px;" >
        <option <?php if ($exp==0) echo 'selected'; ?> value="1">B</option>
        <option <?php if ($exp==1) echo 'selected'; ?> value="1000">KB</option>
        <option <?php if ($exp>1)  echo 'selected'; ?> value="1000000">MB</option>
    </select>
    </fieldset>
    <br>
    
    <input type="submit" class="submit" name="imagizer-save-settings" value="Save settings">
    
</form>
<script>

$('.watermark[type="checkbox"]').bind('change',function(){ 
    console.log('watermark');
    if ($(this).is(':checked') && $('#to-jpeg').is(':checked')) $('.watermark[class~="text"]').removeAttr('disabled');
    else  $('.watermark[class~="text"]').attr('disabled','disabled');
});
$('.compress[type="checkbox"]').bind('change',function(){ 
    console.log('compress');
    if ($(this).is(':checked') && $('#to-jpeg').is(':checked')) $('.compress[class~="text"]').removeAttr('disabled');
    else  $('.compress[class~="text"]').attr('disabled','disabled');
});
$('#to-jpeg').bind('change',function(){ 
    console.log('to-jpeg');
    if ($(this).is(':checked')) $('.to-jpeg').removeAttr('disabled');
    else  $('.to-jpeg').attr('disabled','disabled');
    $('.to-jpeg').change();
    $('.to-jpeg').change();
});

$('#to-jpeg').change();
$('#to-jpeg').change(); 
disable_group("<?php echo $config->size=='var'?'exact':'var';?>");

function disable_group(group_name){
	if (group_name=='exact') {
		$(".exact").attr("readonly","readonly");
		$(".var").removeAttr("readonly");
	}
	else if (group_name=='var') {
		$(".var").attr("readonly","readonly");
		$(".exact").removeAttr("readonly");
	}
}

</script>
