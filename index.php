<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <?php
        // put your code here
        require_once 'slidesharelib.php';
        
        //Initalize slideShare Library!
        $connect = new slidesharelib();

        //Return the slides from a user...
        $data = $connect->call("get_slideshows_by_user", ["username_for" => "GKawasaki", "limit" => "5", "detailed" => 0]);

        //Did everything go well with the web server?
        if($connect->last_http_response !== 200)
        {
          die("An error accured! Please check your params for SlideShare!!");
        }

        print '<strong>Hello SlideShare API!</strong><br/>';
        
        //PRINT ALL THE EMBEDS!
        if(is_array($data["Slideshow"]))
        {
          $slides = $data["Slideshow"];
          foreach($slides as $key => $value)
          {
              print $slides[$key]["Embed"];
          }
        }
        
        ?>
    </body>
</html>
