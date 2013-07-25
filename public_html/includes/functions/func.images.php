<?php
function imagettftextmultisampled(&$hImg, $iSize, $sAngle, $iX, $iY, $cColor, $sFont, $sText, $iMultiSampling){
	$iWidth  = imagesx($hImg);
	$iHeight = imagesy($hImg);
	$hImgCpy = imagecreatetruecolor(ceil($iWidth*$iMultiSampling), ceil($iHeight*$iMultiSampling));
	$cColor  = imagecolorsforindex($hImg, $cColor);
	$cColor  = imagecolorallocatealpha($hImgCpy, $cColor['red'], $cColor['green'], $cColor['blue'], $cColor['alpha']);
	imagesavealpha($hImgCpy, true);
	imagealphablending($hImgCpy, false);
	$cTransparent = imagecolortransparent($hImgCpy, imagecolorallocatealpha($hImgCpy, 255, 0, 255, 127));
	imagefill($hImgCpy, 0, 0, $cTransparent);
	$aBox = imagettftext($hImgCpy, $iSize*$iMultiSampling, $sAngle, ceil($iX*$iMultiSampling), ceil($iY*$iMultiSampling), $cColor, $sFont, $sText);
	imagealphablending($hImg, true);
	imagecopyresampled($hImg, $hImgCpy, 0, 0, 0, 0, $iWidth, $iHeight, ceil($iWidth*$iMultiSampling), ceil($iHeight*$iMultiSampling));
	imagedestroy($hImgCpy);
	foreach($aBox as $iKey => $iCoordinate)
		$aBox[$iKey] = $iCoordinate/$iMultiSampling;
	return($aBox);
}

function imagettfbboxmultisampled($iSize, $iAngle, $sFont, $sText, $iMultiSampling=1){
	$aBox = imagettfbbox($iSize*$iMultiSampling, $iAngle, $sFont, $sText);
	foreach($aBox as $iKey => $iCoordinate)
		$aBox[$iKey] = $iCoordinate/$iMultiSampling;
	return($aBox);
}

if(!function_exists('str_split')) {
    function str_split($string,$string_length=1) {
        if(strlen($string)>$string_length || !$string_length) {
            do {
                $c = strlen($string);
                $parts[] = substr($string,0,$string_length);
                $string = substr($string,$string_length);
            } while($string !== false);
        } else {
            $parts = array($string);
        }
        return $parts;
    }
}

function writeImgText(&$hImg, $iSize, $sAngle, $iX, $iY, $cColor, $sFont, $sText, $iMultiSampling=1, $iKerning=0, $extraSpacer = FALSE) {
	$str_array = str_split($sText);
	$sPrintString = '';
	foreach ($str_array as $key => $sChar) {
		$sPrintString .= $sChar;
		//if (array_search(ucfirst(strtolower($sPrintStringPrev.$sPrintString[0])), array('Bl','Bo','Ch','Do','Dr','Sk')) !== FALSE) { $iX += 2; }
		$aBox = imagettftextmultisampled($hImg, $iSize+$iSizeInc, $sAngle, $iX, $iY, $cColor, $sFont, $sPrintString, $iMultiSampling);
		$iX = $aBox[2]+$iKerning;
		if ($extraSpacer && $key == 0) { $iX += 2; }
		$sPrintStringPrev = $sPrintString;
		$sPrintString = '';
	}
}