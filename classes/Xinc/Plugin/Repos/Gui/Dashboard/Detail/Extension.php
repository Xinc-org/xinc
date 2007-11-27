<?php

class Xinc_Plugin_Repos_Gui_Dashboard_Detail_Extension
{

    const TEMPLATE = '<div class="tab-page" id="%s-page">
<h2 class="tab">%s</h2>
<script type="text/javascript">
%s.addTabPage( document.getElementById( "%s-page" ) );
</script>
%s
</div>
';
    
    
    private $_title;
    
    private $_content;
    
    public function __construct($title)
    {
        $this->_title = $title;
    }
    public function getTitle()
    {
        return $this->_title;
    }
    public function setContent($content)
    {
        $this->_content = $content;
    }
    
    public function getContent()
    {
        return $this->_content;
    }
    
    public function generate($tabPaneName)
    {
        $id = strtolower(str_replace(' ', '-', $this->getTitle()));
        $result = call_user_func_array('sprintf', array(self::TEMPLATE, $id, $this->getTitle(),
                                       $tabPaneName, $id, $this->getContent()));
        
        return $result;
    }
}