<?php
require_once 'ezc/Graph/interfaces/palette.php';

class Xinc_Plugin_Repos_Gui_Statistics_Graph_Palette extends ezcGraphPalette
{
     public function __construct(array $colors)
     {
         $this->dataSetColor = $colors;
     }

 protected $axisColor = '#000000';

 protected $majorGridColor = '#000000BB';

 /**protected $dataSetColor = array(
 '#4E9A0688',
 '#3465A4',
 '#F57900'
 );
*/

 protected $dataSetSymbol = array(
 ezcGraph::BULLET,
 );

protected $fontName = 'sans-serif';

 protected $fontColor = '#555753';
 } 