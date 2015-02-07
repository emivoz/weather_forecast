<?php

/**
 * /application/core/MY_Loader.php
 *
 */
class MY_Loader extends CI_Loader {
    public function template_frontend($template_name, $vars = array(), $return = FALSE)
    {
        $content  = $this->view('templates/frontend/header', $vars, $return);
        $content .= $this->view($template_name, $vars, $return);
        $content .= $this->view('templates/frontend/footer', $vars, $return);

        if ($return)
        {
            return $content;
        }
    }
	
	public function template_backend($template_name, $vars = array(), $return = FALSE)
    {
        $content  = $this->view('templates/backend/header', $vars, $return);
        $content .= $this->view($template_name, $vars, $return);
        $content .= $this->view('templates/backend/footer', $vars, $return);

        if ($return)
        {
            return $content;
        }
    }
}