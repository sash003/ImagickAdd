<?php
error_reporting(E_ALL);
mb_internal_encoding('utf-8');
session_start();



// если файлы  и пути  пришли:
if (!empty($_FILES['image']['name'])){
    define('_PTH', __DIR__.'/');	
	$path = 'images';
	$fullpath = _PTH.$path;
	if ( !file_exists($fullpath) ) mkdir($fullpath, 0755, true);
  	deleteAllFiles($fullpath);
	  $width = 555;
	
			$datimg = pathinfo($_FILES['image']['name']);
			$ext = strtolower($datimg['extension']);				
			$filetypes = array('jpg','png','gif','jpeg');
			// если расширения совпадают
			if (in_array($ext, $filetypes)){
				
			if(!empty($_FILES['waterimage']['name'])){
			$datimgw = pathinfo($_FILES['waterimage']['name']);
			$extw = strtolower($datimgw['extension']);	
			if (in_array($extw, $filetypes)){
                
                if($extw === 'gif'){
                    $dst = microtime(true).'.gif';
                    $dest = $fullpath.'/'.$dst;
                    
                }else{
                    $dst = microtime(true).'.'.$ext;
                    $dest = $fullpath.'/'.$dst;
                }
    			add_watermark($_FILES['image']['tmp_name'], $_FILES['waterimage']['tmp_name'], $dest, $width);
    			echo '<img src="'.$path.'/'.$dst.'">';	
		}
	}else{
          $dst = microtime(true).'.'.$ext;
	      if(resizeUploadImg($_FILES['image']['tmp_name'], $fullpath.'/'.$dst,  $width) )
		  echo '<img src="'.$path.'/'.$dst.'">';
		}
	}
    
	}
    
    	
function resizeUploadImg($orig, $path, $width = 400, $height = 300){
    $result=FALSE;
	try {
		$img = new Imagick($orig);
		if($img->getImageMimeType()=='image/gif'){
			
		foreach ($img as $frame) {
    		$frame->thumbnailImage($width, 0);    		
		}
		/* Обратите внимание, writeImages вместо writeImage */
		$result=$img->writeImages($path, true);
		
	}
		else{
			$img->thumbnailImage($width, 0);
			$img->setImageCompression(Imagick::COMPRESSION_JPEG);
			$img->setImageCompressionQuality(70);
			$result = $img->writeImage($path);
		}
	     $img->clear();
	} catch(Exception $e){
		echo 'У нас проблема '. $e->getMessage(). " в файле ".$e->getFile().", строка ".$e->getLine();
	}
	return $result;
}

function add_watermark( $source_image_path, $watermark_path, $output, $width){
$result = false;
try{
    
	$first = new \Imagick($source_image_path); 
	$second = new \Imagick($watermark_path);

	if($first->getImageMimeType()=='image/gif'){
        
        if($second->getImageMimeType()=='image/gif'){
            
            foreach($second as $frame){
                $frame->thumbnailImage($width/7, 0);
                $frame->setImageOpacity( 0.8);
            }
            if(!file_exists(__DIR__ . '/tmp'))
            mkdir(__DIR__ . '/tmp', 0755, true);
            if(!file_exists(__DIR__ . '/tmp2'))
            mkdir(__DIR__ . '/tmp2', 0755, true);
            $tmppath = __DIR__ . '/tmp/' . uniqid() . microtime(true).'.gif';
            $second->writeImages($tmppath, true);
            
            $second = new \Imagick($tmppath);
            foreach($second as $key=>$frame){
                $frame->writeImage(__DIR__. '/tmp2/' .$key.'.jpg');
            }
            
            $scan = glob('tmp2/*');
            sort($scan, SORT_NATURAL);
            
            foreach($first as $key=>$frame){
                $frame->thumbnailImage($width, 0);
                
                $width_src = $frame->getImageWidth(); // узнаем ширину
        		$height_src = $frame->getImageHeight(); // и высоту
        		$width_mark = $second->getImageWidth(); 
        		$height_mark = $second->getImageHeight(); 
        		$x = $width_src - $width_mark-7;
        		$y = $height_src - $height_mark-7;
                if(array_key_exists($key, $scan)){
                    $new = new \Imagick(realpath($scan[$key]));
                }                
                
                //накладываем изображения
                $frame->compositeImage($new, imagick::COMPOSITE_DEFAULT, $x, $y); 	
                
            }
            $result = $first->writeImages($output, true);
            unlink($tmppath);
            deleteAllFiles('tmp');
            deleteAllFiles('tmp2');
            //////////
            ##########
            
        }else{
        
        // изменим размер "водяого" исходя из переданной ширины
    	$second->thumbnailImage($width/7, 0);
	    $second->setImageOpacity( 0.8);

		// то для каждого фрейма гифки делаем следующее:
		foreach ($first as $frame) {
    		$frame->thumbnailImage($width, 0);
    		$width_src = $first->getImageWidth(); // узнаем ширину
    		$height_src = $first->getImageHeight(); // и высоту
    		$width_mark = $second->getImageWidth(); 
    		$height_mark = $second->getImageHeight(); 
    		$x = $width_src - $width_mark-7;
    		$y = $height_src - $height_mark-7;
            //накладываем изображения
        	$frame->compositeImage($second, imagick::COMPOSITE_DEFAULT, $x, $y);
    	}
		$result=$first->writeImages($output, true);
      }
	}else{
        
		$first->thumbnailImage($width, 0);
        
        if($second->getImageMimeType()=='image/gif'){
            
            $new = new \Imagick();
            
            $delay = getDelay($watermark_path)[0];
            
            foreach($second as $frame){
                $frame->thumbnailImage($width/7, 0);
            }
            $tmppath = __DIR__ . '/tmp/' . uniqid() . microtime(true).'.gif';
            $second->writeImages($tmppath, true);
            
            $second = new \Imagick($tmppath);
            $width_src = $first->getImageWidth(); 
    		$height_src = $first->getImageHeight();
    		$width_mark = $second->getImageWidth();
    		$height_mark = $second->getImageHeight();
    		$x = $width_src - $width_mark-7;
    		$y = $height_src - $height_mark-7;
            
            foreach($second as $frame){

	            $frame->setImageOpacity( 0.8);
                $firt = clone $first;
                
                //накладываем изображения
                $firt->compositeImage($frame, imagick::COMPOSITE_DEFAULT, $x, $y); 	
                $firt->setImageDelay($delay);
                $new->addImage($firt);
            }
            $result = $new->writeImages($output, true);
            unlink($tmppath);
        }else{
    		$first->thumbnailImage($width, 0);
            $second->thumbnailImage($width/7, 0);
    		$width_src = $first->getImageWidth(); 
    		$height_src = $first->getImageHeight();
    		$width_mark = $second->getImageWidth();
    		$height_mark = $second->getImageHeight();
    		$x = $width_src - $width_mark-7;
    		$y = $height_src - $height_mark-7;
    		$first->compositeImage($second, imagick::COMPOSITE_DEFAULT, $x, $y); 	
    		//устанавливаем степень сжатия
    		$first->setImageCompression(Imagick::COMPRESSION_JPEG);
    		//и качество 
    		$first->setImageCompressionQuality(90);
    		$result = $first->writeImage($output);		
    	}
	
	}

$first->clear();
$second->clear();

}
catch(Exception $e){
		echo 'У нас проблема '. $e->getMessage(). " в файле ".$e->getFile().", строка ".$e->getLine();
	}
return $result;
}

function get_mimeType($filename){
    $finfo = finfo_open(FILEINFO_MIME_TYPE); // возвращает mime-тип
    $mime = finfo_file($finfo, $filename);
    finfo_close($finfo);
    return $mime;
}

function delayImage($src, $speed){
    ///$result=FALSE;
    try{
        $imagick = new \Imagick(realpath($src));
        $imagick = $imagick->coalesceImages();

        foreach ($imagick as $frame) {
            $imagick->setImageDelay($speed);
        }

        $imagick = $imagick->deconstructImages();
        $path = __DIR__.'/'.microtime(true).'.gif';
        $imagick->writeImages($path, true);
        return new \Imagick(realpath($path));
    }catch(ImagickException $e){
		echo 'У нас проблема '. $e->getMessage(). " в файле ".$e->getFile().", строка ".$e->getLine();
	}
    //return $result;
}

function getDelay($src){
    $animation = new \Imagick(realpath($src));
    $del = array();
    $countFrames = 0;
    foreach ($animation as $frame) { 
      $delay = $animation->getImageDelay(); 
      $countFrames++;
      $del[] = $delay; 
    } 
    return array($del[0], $countFrames, $del[0]*$countFrames);
}

function deleteAllFiles($dir){
	$list = glob($dir."/*");
	for ($i=0; $i < count($list); $i++){
		if (is_dir($list[$i])) deleteAllFiles ($list[$i]);
		else unlink($list[$i]);
	}
}
