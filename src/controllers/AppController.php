<?php

class AppController {

    protected function render(string $template = null, array $variables = [])
    {
        $templatePath = 'public/views/'. $template.'.html';
        $templatePath404 = 'public/views/404.html';
        $output = "";

        if(file_exists($templatePath)){
            extract($variables);

            ob_start();
            include $templatePath;
            $output = ob_get_clean();
        } else {
            ob_start();
            include $templatePath404;
            $output = ob_get_clean();
        }
        echo $output;
    }

    protected function forbidden()
    {
        http_response_code(403);
        include 'public/views/403.html';
        exit();
    }

    protected function badRequest($message = 'Bad Request')
    {
        http_response_code(400);
        include 'public/views/400.html';
        exit();
    }

}
