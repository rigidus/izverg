<?php

// ����� ��� �������� �������� ���������
class Mailer
{
var $subject;       // (string) ����
var $text;          // (string) ����� ��������� (txt-�������)
var $html;          // (string) ����� ��������� (html-�������)
var $from;          // (string) �� ����
var $to;            // (string) ����
var $charset;       // (string) ��������� (�� ��������� Windows-1251)

var $sHeaders;       // (string)
var $sBody;          // (string)
var $sContentType;   // (string)
var $sHtmlTemplate;  // (string)
var $sBoundary;      // (string)
var $aAttaches;      // (array)

// ����������� ������
function Mailer()
         {
         $this->charset      = 'Windows-1251';
         $this->aAttaches     = array();
         $this->sBoundary     = '----'.substr(md5(uniqid(rand(),true)),0,16);
         $this->sHtmlTemplate = '<html><head><title>{title}</title></head><body>{body}</body></html>';
         }

// �������� ���������
function DoHeader($sHeader)
         {
         $this->sHeaders .= $sHeader."\r\n";
         }

// ���������� ����
function Attach($sPath,$mimeType)
         {
         if (file_exists($sPath))
            {
            $sName=basename($sPath);
            $sAttach ="Content-Type: $mimeType; name=\"$sName\"\r\n";
            $sAttach.="Content-Disposition: attachment; filename=\"$sName\"\r\n";
            $sAttach.="Content-Transfer-Encoding: base64\r\n";
            $sAttach.="\r\n";
            $sAttach.=base64_encode(file_get_contents($sPath))."\r\n";
            $this->aAttaches[] = $sAttach;
            }
         }

// �������� HTML
function AddHtml($sHtml)
         {
         $this->html.=$sHtml."\r\n";
         }

// ���������� ������
function SetTemplate($sPath)
         {
         if (file_exists($sPath)) $this->sHtmlTemplate = file_get_contents($sPath);
         }
// ���������
function Send()
         {
         $iCountAtt=count($this->aAttaches);
         $this->sHeaders ="From: {$this->from}\r\n";
         $this->sHeaders.="MIME-Version: 1.0\r\n";
         if (!$this->html && !$iCountAtt)
            {
            $this->sHeaders.='Content-Type: text/plain; charset='.$this->charset."\r\n";
            $this->sBody = $this->text;
            }
         elseif ($this->html && !$iCountAtt)
                {
                $this->sHeaders.='Content-Type: text/html; charset='.$this->charset."\r\n";
                $aFields=array();
                $aFields['{title}'] = $this->subject;
                $aFields['{body}']  = $this->html;
                $this->sBody = strtr($this->sHtmlTemplate,$aFields);
                }
         elseif (!$this->html && $iCountAtt)
                {
                $this->sHeaders.="Content-Type: multipart/mixed; boundary=\"{$this->sBoundary}\"\r\n";
                foreach ($this->aAttaches as $sAttach)
                        {
                        $this->sBody .= "--{$this->sBoundary}\r\n";
                        $this->sBody .= $sAttach;
                        }
                $this->sBody .= "--{$this->sBoundary}--\r\n";
                }
         elseif ($this->html && $iCountAtt)
                {
                $this->sHeaders.="Content-Type: multipart/mixed; boundary=\"{$this->sBoundary}\"\r\n";
                $this->sBody .= "--{$this->sBoundary}\r\n";
                $this->sBody .= "Content-Type: text/html; charset={$this->charset}\r\n";
                $this->sBody .= "Content-Transfer-Encoding: 8bit\r\n";
                $this->sBody .= "\r\n";
                $aFields=array();
                $aFields['{title}'] = $this->subject;
                $aFields['{body}']  = $this->html;
                $this->sBody .= strtr($this->sHtmlTemplate,$aFields);
                foreach ($this->aAttaches as $sAttach)
                        {
                        $this->sBody .= "--{$this->sBoundary}\r\n";
                        $this->sBody .= $sAttach;
                        }
                $this->sBody .= "--{$this->sBoundary}--\r\n";
                }
         @mail($this->to, $this->subject, $this->sBody, $this->sHeaders);
         }

} // End of class Mailer

?>