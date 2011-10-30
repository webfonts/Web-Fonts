<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined ('_JEXEC') or die();

class StyleDeclaration {

  static public function font($name, $eot, $woff, $ttf, $svg){
    return <<<FONT
    @font-face	
      {
        font-family: "{$name}";
        src: url("{$eot}#") format("eot");
      }
      @font-face	
      {
        font-family: "{$name}";
        src: url("{$eot}#");
        src: url("{$woff}") format("woff"),
             url("{$ttf}") format("truetype"),
             url("{$svg}") format("svg");
      }


FONT;
  }
}
