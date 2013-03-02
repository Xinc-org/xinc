<?php
class Page
{ 
    private $output;

    public function __construct()
    {
        $this->output = '<h1>Page output</h1>';
    }

    public function getOutput()
    {
        return $this->output;
    }
}
