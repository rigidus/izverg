<?php
class Lingua_Stem_Ru
{
    var $VERSION = "0.02";
    var $Stem_Caching = 0;
    var $Stem_Cache = array();
    var $VOWEL = '/���������/';
    var $PERFECTIVEGROUND = '/((��|����|������|��|����|������)|((?<=[��])(�|���|�����)))$/';
    var $REFLEXIVE = '/(�[��])$/';
    var $ADJECTIVE = '/(��|��|��|��|���|���|��|��|��|��|��|��|��|��|���|���|���|���|��|��|��|��|��|��|��|��)$/';
    var $PARTICIPLE = '/((���|���|���)|((?<=[��])(��|��|��|��|�)))$/';
    var $VERB = '/((���|���|���|����|����|���|���|���|��|��|��|��|��|��|��|���|���|���|��|���|���|��|��|���|���|���|���|��|�)|((?<=[��])(��|��|���|���|��|�|�|��|�|��|��|��|��|��|��|���|���)))$/';
    var $NOUN = '/(�|��|��|��|��|�|����|���|���|��|��|�|���|��|��|��|�|���|��|���|��|��|��|�|�|��|���|��|�|�|��|��|�|��|��|�)$/';
    var $RVRE = '/^(.*?[���������])(.*)$/';
    var $DERIVATIONAL = '/[^���������][���������]+[^���������]+[���������].*(?<=�)���?$/';

    function s(&$s, $re, $to)
    {
        $orig = $s;
        $s = preg_replace($re, $to, $s);
        return $orig !== $s;
    }

    function m($s, $re)
    {
        return preg_match($re, $s);
    }

    function stem_word($word)
    {
        $word = strtolower($word);
        //$word = strtr($word, '�', '�');
        $word = strtr($word, array('�'=>'�')); 
        # Check against cache of stemmed words
        if ($this->Stem_Caching && isset($this->Stem_Cache[$word])) {
            return $this->Stem_Cache[$word];
        }
        $stem = $word;
        do {
          if (!preg_match($this->RVRE, $word, $p)) break;
          $start = $p[1];
          $RV = $p[2];
          if (!$RV) break;

          # Step 1
          if (!$this->s($RV, $this->PERFECTIVEGROUND, '')) {
              $this->s($RV, $this->REFLEXIVE, '');

              if ($this->s($RV, $this->ADJECTIVE, '')) {
                  $this->s($RV, $this->PARTICIPLE, '');
              } else {
                  if (!$this->s($RV, $this->VERB, ''))
                      $this->s($RV, $this->NOUN, '');
              }
          }

          # Step 2
          $this->s($RV, '/�$/', '');

          # Step 3
          if ($this->m($RV, $this->DERIVATIONAL))
              $this->s($RV, '/����?$/', '');

          # Step 4
          if (!$this->s($RV, '/�$/', '')) {
              $this->s($RV, '/����?/', '');
              $this->s($RV, '/��$/', '�');
          }

          $stem = $start.$RV;
        } while(false);
        if ($this->Stem_Caching) $this->Stem_Cache[$word] = $stem;
        return $stem;
    }

    function stem_caching($parm_ref)
    {
        $caching_level = @$parm_ref['-level'];
        if ($caching_level) {
            if (!$this->m($caching_level, '/^[012]$/')) {
                die(__CLASS__ . "::stem_caching() - Legal values are '0','1' or '2'. '$caching_level' is not a legal value");
            }
            $this->Stem_Caching = $caching_level;
        }
        return $this->Stem_Caching;
    }

    function clear_stem_cache()
    {
        $this->Stem_Cache = array();
    }
}


// ===============================================================================




/*
*  PHP5 implementation of Martin Porter's stemming algorithm for Russian language.
*  Written on a cold winter evening close to the end of 2005 by Dennis Kreminsky (etranger at etranger dot ru)
*  Use the code freely, but don't hold me responsible if it breaks whatever it might break.
*
*
*  Usage:
*  $stem=stem::russian($word);
*  All Russian characters are (originally) in UTF-8.
*
*/

/*
	�������� ��������� �������� ��� win1251 � php4 , ������ ��� ���� ���� ������ ���� �������� ;) 
	serge@rogozhkin.ru 
*/


define ('CHAR_LENGTH', '1'); // all Russian characters take 2 bytes in UTF-8, so instead of using (not supported by default) mb_*
                             // string functions, we use the standard ones with a dirty char-length trick.
                             // Should you want to use WIN-1251 (or any other charset), convert this source file to that encoding
                             // and then change CHAR_LENGTH to the proper value, which is likely to be '1' then.

class PorterStem {

	var $_abc;
	var $_ABC;

	function PorterStem() {
		$this->_abc = '�������������������������������';
		$this->_ABC = '�����Ũ�������������������������';
	}

	function rustolower($arg){
		for($i=0;$i<strlen($this->_abc);$i++){
			$arg = str_replace($this->_ABC{$i},$this->_abc{$i},$arg);
		}
		return $arg;
	}

 function russian($word)
   {
   	 // RUSSIAN DIRTY LOWERCASE:
   		$word = $this->rustolower($word);
   
      $a=$this->rv($word);
      $start=$a[0];
      $rv=$a[1];
      $rv=$this->step1($rv);
      $rv=$this->step2($rv);
      $rv=$this->step3($rv);
      $rv=$this->step4($rv);
      return $start.$rv;
   }

 function rv($word)
   {
      $vowels=array('�','�','�','�','�','�','�','�','�');
      $flag=0;
      $rv='';
      $start='';
      for ($i=0; $i<strlen($word); $i+=CHAR_LENGTH)
         {
            if ($flag==1)
               $rv.=substr($word, $i, CHAR_LENGTH);
            else
               $start.=substr($word, $i, CHAR_LENGTH);
            if (array_search(substr($word,$i,CHAR_LENGTH), $vowels)!==FALSE)
               $flag=1;
         }
      return array($start,$rv);
   }

 function step1($word)
   {
      $perfective1=array('�', '���', '�����');
      foreach ($perfective1 as $suffix)
          if (substr($word,-(strlen($suffix)))==$suffix && (substr($word,-strlen($suffix)-CHAR_LENGTH,CHAR_LENGTH)=='�' || substr($word,-strlen($suffix)-CHAR_LENGTH,CHAR_LENGTH)=='�'))
            return substr($word, 0, strlen($word)-strlen($suffix));

      $perfective2=array('��','����','������','����','������');
      foreach ($perfective2 as $suffix)
          if (substr($word,-(strlen($suffix)))==$suffix)
            return substr($word, 0, strlen($word)-strlen($suffix));

      $reflexive=array('��', '��');
      foreach ($reflexive as $suffix)
          if (substr($word,-(strlen($suffix)))==$suffix)
            $word=substr($word, 0, strlen($word)-strlen($suffix));

      $adjective=array('��','��','��','��','���','���','��','��','��','��','��','��','��','��','���','���','���','���','��','��','��','��','��','��','��','��');
      $participle2=array('��','��','��','��','�');
      $participle1=array('���','���','���');
      foreach ($adjective as $suffix)
          if (substr($word,-(strlen($suffix)))==$suffix)
            {
             $word=substr($word, 0, strlen($word)-strlen($suffix));
             foreach ($participle1 as $suffix)
                if (substr($word,-(strlen($suffix)))==$suffix && (substr($word,-strlen($suffix)-CHAR_LENGTH,CHAR_LENGTH)=='�' || substr($word,-strlen($suffix)-CHAR_LENGTH,CHAR_LENGTH)=='�'))
                  $word=substr($word, 0, strlen($word)-strlen($suffix));
             foreach ($participle2 as $suffix)
                if (substr($word,-(strlen($suffix)))==$suffix)
                  $word=substr($word, 0, strlen($word)-strlen($suffix));
             return $word;
            }

      $verb1=array('��','��','���','���','��','�','�','��','�','��','��','��','��','��','��','���','���');
      foreach ($verb1 as $suffix)
          if (substr($word,-(strlen($suffix)))==$suffix && (substr($word,-strlen($suffix)-CHAR_LENGTH,CHAR_LENGTH)=='�' || substr($word,-strlen($suffix)-CHAR_LENGTH,CHAR_LENGTH)=='�'))
            return substr($word, 0, strlen($word)-strlen($suffix));

      $verb2=array('���','���','���','����','����','���','���','���','��','��','��','��','��','��','��','���','���','���','��','���','���','��','��','���','���','���','���','��','�');
      foreach ($verb2 as $suffix)
          if (substr($word,-(strlen($suffix)))==$suffix)
            return substr($word, 0, strlen($word)-strlen($suffix));

      $noun=array('�','��','��','��','��','�','����','���','���','��','��','�','���','��','��','��','�','���','��','���','��','��','��','�','�','��','���','��','�','�','��','��','�','��','��','�');
      foreach ($noun as $suffix)
          if (substr($word,-(strlen($suffix)))==$suffix)
            return substr($word, 0, strlen($word)-strlen($suffix));

      return $word;
   }

 function step2($word)
   {
      if (substr($word,-CHAR_LENGTH,CHAR_LENGTH)=='�')
            $word=substr($word, 0, strlen($word)-CHAR_LENGTH);
      return $word;
   }

 function step3($word)
   {
      $vowels=array('�','�','�','�','�','�','�','�','�');
      $flag=0;
      $r1='';
      $r2='';
      for ($i=0; $i<strlen($word); $i+=CHAR_LENGTH)
         {
            if ($flag==2)
               $r1.=substr($word, $i, CHAR_LENGTH);
            if (array_search(substr($word,$i,CHAR_LENGTH), $vowels)!==FALSE)
               $flag=1;
            if ($flag=1 && array_search(substr($word,$i,CHAR_LENGTH), $vowels)===FALSE)
               $flag=2;
         }
      $flag=0;
      for ($i=0; $i<strlen($r1); $i+=CHAR_LENGTH)
         {
            if ($flag==2)
               $r2.=substr($r1, $i, CHAR_LENGTH);
            if (array_search(substr($r1,$i,CHAR_LENGTH), $vowels)!==FALSE)
               $flag=1;
            if ($flag=1 && array_search(substr($r1,$i,CHAR_LENGTH), $vowels)===FALSE)
               $flag=2;
         }
      $derivational=array('���', '����');
      foreach ($derivational as $suffix)
          if (substr($r2,-(strlen($suffix)))==$suffix)
            $word=substr($word, 0, strlen($r2)-strlen($suffix));
      return $word;
   }

 function step4($word)
   {
      if (substr($word,-CHAR_LENGTH*2)=='��')
            $word=substr($word, 0, strlen($word)-CHAR_LENGTH);
      else
         {
            $superlative=array('���', '����');
            foreach ($superlative as $suffix)
                if (substr($word,-(strlen($suffix)))==$suffix)
                  $word=substr($word, 0, strlen($word)-strlen($suffix));
            if (substr($word,-CHAR_LENGTH*2)=='��')
                $word=substr($word, 0, strlen($word)-CHAR_LENGTH);
         }
      // should there be a guard flag? can't think of a russian word that ends with ���� or ��� anyways, though the algorithm states this is an "otherwise" case
      if (substr($word,-CHAR_LENGTH,CHAR_LENGTH)=='�')
            $word=substr($word, 0, strlen($word)-CHAR_LENGTH);
      return $word;
   }
}


?>