<?php
class Page
{ 
    /**
     * Holding the pages output
     *
     * @var string
     */
    private $output;

    /**
     * Constructor,
     * sets the output for the page
     *
     */
    public function __construct()
    {
        $this->output = '<h1>Page output</h1>';
    }

    /**
     * Returns the output
     *
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }
}
