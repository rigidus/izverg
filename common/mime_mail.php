<?php

/*
* Class mime_mail
* Original implementation by Sascha Schumann <sascha@schumann.cx>
* Modified by Tobias Ratschiller &lt;tobias@dnet.it>:
* - General code clean-up
* - separate body- and from-property
* - killed some mostly un-necessary stuff
*/

/*
$mail = new mime_mail();
$mail->from = "noreply@svyaz.spb.ru";
$mail->to = $v['email'];
$mail->subject =  $tplSubject->get();
$mail->body = '<html>
				 <head>
				 </head>
			   <body>
			     <h1>'.$tplTitle->get().'</h1>
			     <p>'.$tplText->get().'<p>
			   </body>
			   </html>';
$mail->add_attachment($aMail['file'], $aMail['filename'], $mimetype);
$mail->send();

// —формировать письмо
$oMail = new mime_mail();
$oMail->from 	= "noreply@svyaz.spb.ru";
$oMail->to 		= $aAddr['email'];
	$tplSubject = new KTemplate();
	$tplSubject->loadTemplateContent($oS->title);
	$tplSubject->assign($aAddr);
	$tplSubject->reset();
$oMail->subject = $tplSubject->get();
	$tplMessage = new KTemplate();
	$tplMessage->loadTemplateContent('
	<html>
	<head>
	<title>'.$oS->title.'</title>
	</head>
	<body>
	<h1>'.$oS->title.'</h1>
	'.$oS->message.'
	</body>
	</html>
	');
	$tplMessage->assign($aAddr);
$oMail->body 	= $tplMessage->get();
foreach ($aFiles as $fk=>$fv) {
	$oMail->add_attachment($fv['filecontent'], $fv['filename']);
}
dbg($oMail);
//$oMail->send();	



*/

class mime_mail
{
	var $parts;
	var $to;
	var $from;
	var $headers;
	var $subject;
	var $body;
	
	/*
	* void mime_mail()
	* class constructor
	*/
	function mime_mail()
	{
		$this->parts = array();
		$this->to = "";
		$this->from = "";
		$this->subject = "";
		$this->body = "";
		$this->headers = "";
	}
	
	/*
	* void add_attachment(string message, [string name], [string ctype])
	* Add an attachment to the mail object
	*/
	function add_attachment($message, $name = "", $ctype = "application/octet-stream")
	{
		$this->parts[] = array (
			"ctype" => $ctype,
			"message" => $message,
			"name" => $name
		);
	}
	
	/*
	* void build_message(array part=
	* Build message parts of an multipart mail
	*/
	function build_message($part)
	{
		$message = $part[ "message"];
		$message = chunk_split(base64_encode($message));
		$encoding = "base64";
		return "Content-Type: ".$part[ "ctype"].
		($part[ "name"]? "; name = \"".$part[ "name"]. "\"" : "").
		//"\nContent-Disposition: attachment".
		"\nContent-Transfer-Encoding: $encoding\n\n$message\n";
	}
	
	/*
	* void build_multipart()
	* Build a multipart mail
	*/
	function build_multipart()
	{
		$boundary = "b".md5(uniqid(time()));
		$multipart = "Content-Type: multipart/mixed; boundary = $boundary\n\nThis is a MIME encoded message.\n\n--$boundary";
		for($i = sizeof($this->parts)-1; $i >= 0; $i--)	{
			$multipart .= "\n".$this->build_message($this->parts[$i]). "--$boundary";
		}
		return $multipart.= "--\n";
	}
	
	/*
	* void send()
	* Send the mail (last class-function to be called)
	*/
	function send()
	{
		$mime = "";
		if (!empty($this->from)) {
			$mime .= "From: ".$this->from. "\n";
			$mime .= "Return-Path: ".$this->from. "\n";
		} else {
			//print "Error!!! Not completed the From field";
			//exit;
		}
	
		if (!empty($this->headers))
		$mime .= $this->headers. "\n";
		
		if (!empty($this->body))
			$this->add_attachment($this->body, "", "text/html; charset=windows-1251");
		$mime .= "MIME-Version: 1.0\n".$this->build_multipart();
		mail($this->to, $this->subject, "", $mime);
	}
	
	function get_mime_type($ext)
	{
		$aMimeTypes = array(
		'application/msword'=>'doc',
		'application/octet-stream'=>'bin dms lha lzh exe class so dll',
		'application/ogg'=>'ogg',
		'application/pdf'=>'pdf',
		'application/rar'=>'rar',
		'application/x-javascript'=>'js',
		'application/x-shockwave-flash'=>'swf',
		'application/x-tar'=>'tar',
		'application/xml'=>'xml xsl',
		'application/zip '=>'zip',
		'audio/midi'=>'mid midi kar',
		'audio/mpeg'=>'mpga mp2 mp3',
		'audio/x-wav'=>'wav',
		'image/bmp'=>'bmp',
		'image/gif'=>'gif',
		'image/jpeg'=>'jpeg jpg jpe',
		'image/png'=>'png',
		'text/css'=>'css',
		'text/html'=>'html htm',
		'text/php'=>'php php3 php4 phtml',
		'text/plain'=>'asc txt',
		'text/rtf'=>'rtf',
		'text/xml'=>'xml',
		'video/mpeg'=>'mpeg mpg mpe',
		'video/quicktime'=>'qt mov',		
		);
		$mimetype = '';
		foreach ($aMimeTypes as $k=>$v) {
			if (false !== strpos($v, $ext)) {
				$mimetype = $k;
				break;
			}
		}
		if ($mimetype = '') {
			$mimetype = 'application/octet-stream';
		}
		return $mimetype;
	}
}

?>