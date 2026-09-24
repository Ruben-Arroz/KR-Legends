<?php
$to = "manuelarroz@gmail.com";
$subject = "Titulo do assunto do email";
$txt = "Hello world!";
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
// $headers .= "From: a29621@soaresbasto.pt";
echo mail($to,$subject,$txt,$headers);
?>