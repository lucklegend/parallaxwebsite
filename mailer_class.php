<?php
/*
Attachment Mailer class - version 1.01
available at http://www.finalwebsites.com 
*/

define("LIBR", "\r\n"); // use a "\n" if you have problems

class attach_mailer {
	
	var $from_name;
	var $from_mail;
	var $mail_to;
	var $mail_cc;
	var $mail_bcc;
	
	var $mail_headers;
	var $mail_subject;
	var $mail_body = "";
	
	var $valid_mail_adresses; // boolean is true if all mail(to) adresses are valid
	var $uid; // the unique value for the mail boundry
	var $mail_priority = 3; // 3 = normal, 2 = high, 4 = low
	
	var $att_files = array();
	var $msg = array();
	
	// functions inside this constructor
	// - validation of e-mail adresses
	// - setting mail variables
	// - setting boolean $valid_mail_adresses
	function attach_mailer($name = "", $from, $to, $cc = "", $bcc = "", $subject = "", $body = "") {
		$this->valid_mail_adresses = true;
		if (!$this->check_mail_address($to)) {
			$this->msg[] = "Error, the \"mailto\" address is empty or not valid.";
			$this->valid_mail_adresses = false;
		} 
		if (!$this->check_mail_address($from)) {
			$this->msg[] = "Error, the \"from\" address is empty or not valid.";
			$this->valid_mail_adresses = false;
		} 
		if ($cc != "") {
			if (!$this->check_mail_address($cc)) {
				$this->msg[] = "Error, the \"Cc\" address is not valid.";
				$this->valid_mail_adresses = false;
			} 
		}
		if ($bcc != "") {
			if (!$this->check_mail_address($bcc)) {
				$this->msg[] = "Error, the \"Bcc\" address is not valid.";
				$this->valid_mail_adresses = false;
			} 
		}
		if ($this->valid_mail_adresses) {
			$this->from_name = $this->strip_line_breaks($name);
			$this->from_mail = $this->strip_line_breaks($from);
			$this->mail_to = $this->strip_line_breaks($to);
			$this->mail_cc = $this->strip_line_breaks($cc);
			$this->mail_bcc = $this->strip_line_breaks($bcc);
			$this->mail_subject = $this->strip_line_breaks($subject);
			$this->create_mime_boundry();
			$this->mail_body = $this->create_msg_body($body);
			$this->mail_headers = $this->create_mail_headers();
		} else {
			return;
		}		
	}
	function get_msg_str() {
		$messages = "";
		foreach($this->msg as $val) {
			$messages .= $val."<br>\n";
		}
		return $messages;			
	}
	// use this to prent formmail spamming
	function strip_line_breaks($val) {
		$val = preg_replace("/([\r\n])/", "", $val);
		return $val;
	}
	function check_mail_address($mail_address) {
		$pattern = "/^[\w-]+(\.[\w-]+)*@([0-9a-z][0-9a-z-]*[0-9a-z]\.)+([a-z]{2,4})$/i";
		if (preg_match($pattern, $mail_address)) {
			if (function_exists("checkdnsrr")) {
				$parts = explode("@", $mail_address);
				if (checkdnsrr($parts[1], "MX")){
					return true;
				} else {
					return false;
				}
			} else {
				// on windows hosts is only a limited e-mail address validation possible
				return true;
			}
		} else {
			return false;
		}
	}
	function create_mime_boundry() {
		$this->uid = "_".md5(uniqid(time()));
	}
	function get_file_data($filepath) {
		if (file_exists($filepath)) {
			if (!$str = file_get_contents($filepath)) {
				$this->msg[] = "Error while opening attachment \"".basename($filepath)."\"";
			} else {
				return $str;
			}
		} else {
			$this->msg[] = "Error, the file \"".basename($filepath)."\" does not exist.";
			return;
		}
	}
	// remember "LIBR" is the line break defined in constact above
	function create_msg_body($mail_msg, $cont_tranf_enc = "7bit", $type = "text/html", $enc = "iso-8859-1") {
//		$str = "--".$this->uid.LIBR;
//		$str .= "Content-type:".$type."; charset=".$enc.LIBR;
//		$str .= "Content-Transfer-Encoding: ".$cont_tranf_enc.LIBR.LIBR;
		$str = trim($mail_msg).LIBR.LIBR;
		return $str;
	}
	function create_mail_headers() {
//		if ($this->from_name != "") {
//			$headers = "From: ".$this->from_name." <".$this->from_mail.">".LIBR;
//			$headers .= "Reply-To: ".$this->from_name." <".$this->from_mail.">".LIBR;
//		} else {
//			$headers = "From: ".$this->from_mail.LIBR;
//			$headers .= "Reply-To: ".$this->from_mail.LIBR;
//		}
//		if ($this->mail_cc != "") $headers .= "Cc: ".$this->mail_cc.LIBR;
//		if ($this->mail_bcc != "") $headers .= "Bcc: ".$this->mail_bcc.LIBR;
//		$headers .= "MIME-Version: 1.0".LIBR;
//		$headers .= "X-Mailer: Attachment Mailer ver. 1.0".LIBR;
//		$headers .= "X-Priority: ".$this->mail_priority.LIBR;
//		$headers .= "Content-Type: multipart/mixed;".LIBR.chr(9)." boundary=\"".$this->uid."\"".LIBR.LIBR;
//		$headers .= "This is a multi-part message in MIME format.".LIBR.LIBR;
//		
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= "From: ".$this->from_name." <".$this->from_mail.">" . "\r\n";
		$headers .= 'Cc: ' . $this->mail_cc  . "\r\n";
		$headers .= 'Bcc: ' . $this->mail_bcc . "\r\n";		
//		echo $headers;
//		exit;
		return $headers;
	}
	// use for $dispo "attachment" or "inline" (f.e. example images inside a html mail
	function create_attachment_part($file, $dispo = "attachment") {
		if (!$this->valid_mail_adresses) return;
		$file_str = $this->get_file_data($file);
		if ($file_str == "") {
			return;
		} else {
		
//			$finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
//			$file_type = finfo_file($finfo, $file);
//			finfo_close($finfo);
			$filename = basename($file);
			$file_type = $this->file_type($file);
			$chunks = chunk_split(base64_encode($file_str));
			$mail_part = "--".$this->uid.LIBR;
			$mail_part .= "Content-type:".$file_type.";".LIBR.chr(9)." name=\"".$filename."\"".LIBR;
			$mail_part .= "Content-Transfer-Encoding: base64".LIBR;
			$mail_part .= "Content-Disposition: ".$dispo.";".chr(9)."filename=\"".$filename."\"".LIBR.LIBR;
			$mail_part .= $chunks;
			$mail_part .= LIBR.LIBR;
			$this->att_files[] = $mail_part;
		}			
	}
	function process_mail() {
		if (!$this->valid_mail_adresses) return;
		$mail_message = $this->mail_body;
		if (count($this->att_files) > 0) {
			foreach ($this->att_files as $val) {
				$mail_message .= $val;
			}
			$mail_message .= "--".$this->uid."--";
		}
		if (mail($this->mail_to, $this->mail_subject, $mail_message, $this->mail_headers)) {
			$this->msg[] = "Your mail is succesfully submitted.";
		} else {
			$this->msg[] = "Error while sending you mail.";
		}
	}
	
	
	

    function file_type($filename) {
		if(!function_exists('mime_content_type')) {
			$mime_types = array(
	
				'txt' => 'text/plain',
				'htm' => 'text/html',
				'html' => 'text/html',
				'php' => 'text/html',
				'css' => 'text/css',
				'js' => 'application/javascript',
				'json' => 'application/json',
				'xml' => 'application/xml',
				'swf' => 'application/x-shockwave-flash',
				'flv' => 'video/x-flv',
	
				// images
				'png' => 'image/png',
				'jpe' => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'jpg' => 'image/jpeg',
				'gif' => 'image/gif',
				'bmp' => 'image/bmp',
				'ico' => 'image/vnd.microsoft.icon',
				'tiff' => 'image/tiff',
				'tif' => 'image/tiff',
				'svg' => 'image/svg+xml',
				'svgz' => 'image/svg+xml',
	
				// archives
				'zip' => 'application/zip',
				'rar' => 'application/x-rar-compressed',
				'exe' => 'application/x-msdownload',
				'msi' => 'application/x-msdownload',
				'cab' => 'application/vnd.ms-cab-compressed',
	
				// audio/video
				'mp3' => 'audio/mpeg',
				'qt' => 'video/quicktime',
				'mov' => 'video/quicktime',
	
				// adobe
				'pdf' => 'application/pdf',
				'psd' => 'image/vnd.adobe.photoshop',
				'ai' => 'application/postscript',
				'eps' => 'application/postscript',
				'ps' => 'application/postscript',
	
				// ms office
				'doc' => 'application/msword',
				'rtf' => 'application/rtf',
				'xls' => 'application/vnd.ms-excel',
				'ppt' => 'application/vnd.ms-powerpoint',
	
				// open office
				'odt' => 'application/vnd.oasis.opendocument.text',
				'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
			);

			$ext = strtolower(array_pop(explode('.',$filename)));
			if (array_key_exists($ext, $mime_types)) {
				return $mime_types[$ext];
			} elseif (function_exists('finfo_open')) {
				$finfo = finfo_open(FILEINFO_MIME);
				$mimetype = finfo_file($finfo, $filename);
				finfo_close($finfo);
				return $mimetype;
			}
			else {
				return 'application/octet-stream';
			}
    	}
	}
}

function cancel_log($body){
	$file = dirname(__FILE__) . "/cancel_log.txt";
	if (file_exists($file) && !empty($body)){
		if (filesize($file) <= 1048576){
			$handle = fopen ($file, 'a+'); 
		} else {
			rename("cancel_log.txt", "cancel_log" . date('Ymd') . ".txt");
			$handle = fopen ($file, 'a+'); 
		}
	}
	if(is_resource($handle)){
		fwrite ($handle, "\n" . date('Y m d H:i'));
		fwrite ($handle, "\n" . $body);
		fclose ($handle);  
	}
}  
 
?> 
