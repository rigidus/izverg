<?php

/**
 * (php5only)
 * http://forum.dklab.ru/php/advises/Strip_tags_smartKorrektniyAnalogStrip_tags.html
 * ����� ����������� ������ strip_tags() ��� ����������� ��������� ����� �� html ����.
 * ������� strip_tags(), � ����������� �� ���������, ����� �������� �� ���������.
 * ����������:
 *   - ��������� �������������� ��������� ���� "a < b > c"
 *   - ��������� �������������� "�������" html, ����� � ��������� ��������� ����� ����� ����������� ������� < >
 *   - ��������� �������������� �������� html
 *   - ���������� �����������, �������, �����, PHP, Perl, ASP ���, MS Word ����, CDATA
 *   - ������������� ������������� �����, ���� �� �������� html ���
 *   - ������ �� �������� ����: "<<fake>script>alert('hi')</</fake>script>"
 *
 * @param   string  $s
 * @param   array   $allowable_tags     ������ �����, ������� �� ����� ��������
 * @param   bool    $is_format_spaces   ������������� ������� � �������� �����?
 *                                      ����� ������������� �������������, ���� �� �������� html ���:
 *                                      ��� ������ �� ������ (plain) ����������� ������������ ���� ������ � �������� �� �����
 *                                      ������� �������, �������� ����������� text/html � text/plain
 *                                      ���� ����� �������� html ����, $is_format_spaces = TRUE
 * @param   array   $pair_tags   ������ ��� ������ �����, ������� ����� ������� ������ � ����������
 *                               ��. �������� �� ���������
 * @param   array   $para_tags   ������ ��� ������ �����, ������� ����� �������������� ��� ��������� (���� $is_format_spaces = true)
 *                               ��. �������� �� ���������
 * @return  string
 *
 * @author   Nasibullin Rinat <n a s i b u l l i n  at starlink ru>
 * @charset  ANSI
 * @version  4.0.5
 */
function strip_tags_smart(
    /*string*/ $s,
    array $allowable_tags = null,
    /*boolean*/ $is_format_spaces = false,
    array $pair_tags = array('script', 'style', 'map', 'iframe', 'frameset', 'object', 'applet', 'comment', 'button'),
    array $para_tags = array('p', 'td', 'th', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'form', 'title', 'pre', 'textarea')
)
{
    static $_callback_type  = false;
    static $_allowable_tags = array();
    static $_para_tags      = array();
    #���������� ��������� ��� ��������� �����
    #��������� ������������ ������� � ����� HTML � ������������ ��� UTF-8 ���������!
    static $re_attrs_fast_safe =  '(?> (?>[\x20\r\n\t]+|\xc2\xa0)+  #���������� ������� (�.�. �����������)
                                       (?>
                                         #���������� ��������
                                                                        [^>"\']+
                                         | (?<=[\=\x20\r\n\t]|\xc2\xa0) "[^"]*"
                                         | (?<=[\=\x20\r\n\t]|\xc2\xa0) \'[^\']*\'
                                         #�������� ��������
                                         |                              [^>]+
                                       )*
                                   )?';

    if (is_array($s))
    {
        if ($_callback_type === 'strip_tags')
        {
            $tag = strtolower($s[1]);
            if ($_allowable_tags &&
                (array_key_exists($tag, $_allowable_tags) || array_key_exists('<' . trim(strtolower($s[0]), '< />') . '>', $_allowable_tags))
                ) return $s[0];
            if ($tag == 'br') return "\r\n";
            if ($_para_tags && array_key_exists($tag, $_para_tags)) return "\r\n\r\n";
            return '';
        }
        if ($_callback_type === 'strip_spaces')
        {
            if (substr($s[0], 0, 1) === '<') return $s[0];
            return ' ';
        }
        trigger_error('Unknown callback type "' . $_callback_type . '"!', E_USER_ERROR);
    }

    if (($pos = strpos($s, '<')) === false || strpos($s, '>', $pos) === false)  #����������� ��������
    {
        #���� �� �������
        return $s;
    }

    #�������� ���� (�����������, �����������, !DOCTYPE, MS Word namespace)
    $re_tags = '/<[\/\!]? ([a-zA-Z][a-zA-Z\d]* (?>\:[a-zA-Z][a-zA-Z\d]*)?)' . $re_attrs_fast_safe . '\/?>/sx';

    $patterns = array(
        '/<([\?\%]) .*? \\1>/sx',     #���������� PHP, Perl, ASP ���
        '/<\!\[CDATA\[ .*? \]\]>/sx', #����� CDATA
        #'/<\!\[  [\x20\r\n\t]* [a-zA-Z] .*?  \]>/sx',  #:DEPRECATED: MS Word ���� ���� <![if! vml]>...<![endif]>

        '/<\!--.*?-->/s', #�����������

        #MS Word ���� ���� "<![if! vml]>...<![endif]>",
        #�������� ���������� ���� ��� IE ���� "<!--[if expression]> HTML <![endif]-->"
        #�������� ���������� ���� ��� IE ���� "<![if expression]> HTML <![endif]>"
        #��. http://www.tigir.com/comments.htm
        '/<\! (?>--)?
              \[
              (?> [^\]"\']+ | "[^"]*" | \'[^\']*\' )*
              \]
              (?>--)?
         >/sx',
    );
    if ($pair_tags)
    {
        #������ ���� ������ � ����������:
        foreach ($pair_tags as $k => $v) $pair_tags[$k] = preg_quote($v, '/');
        $patterns[] = '/<((?i:' . implode('|', $pair_tags) . '))' . $re_attrs_fast_safe . '> .*? <\/(?i:\\1)' . $re_attrs_fast_safe . '>/sx';
    }
    #d($patterns);

    $i = 0; #������ �� ������������
    $max = 99;
    while ($i < $max)
    {
        $s2 = preg_replace($patterns, '', $s);
        if ($i == 0)
        {
            $is_html = ($s2 != $s || preg_match($re_tags, $s2));
            if ($is_html)
            {
                #� ���������� PCRE ��� PHP \s - ��� ����� ���������� ������, � ������ ����� �������� [\x09\x0a\x0c\x0d\x20\xa0] ���, �� �������, [\t\n\f\r \xa0]
                #���� \s ������������ � ������������� /u, �� \s ���������� ��� [\x09\x0a\x0c\x0d\x20]
                #������� �� ������ �������� ����� ����������� ���������,
                #���� �� ������ ������ ������ ������� �������������� ��� ����
                #$s2 = str_replace(array("\r", "\n", "\t"), ' ', $s2);
                #$s2 = strtr($s2, "\x09\x0a\x0c\x0d", '    ');
                
                $_callback_type = 'strip_spaces';
                $s2 = preg_replace_callback('/  [\x09\x0a\x0c\x0d]+
                                              | <((?i:pre|textarea))' . $re_attrs_fast_safe . '>
                                                .+?
                                                <\/(?i:\\1)' . $re_attrs_fast_safe . '>
                                             /sx', __FUNCTION__, $s2);
                $_callback_type = false;

                #������ �����, ������� �� ����� ��������
                if ($allowable_tags) $_allowable_tags = array_flip($allowable_tags);

                #������ ����, ������� ����� �������������� ��� ���������
                if ($para_tags) $_para_tags = array_flip($para_tags);
            }
        }#if

        #��������� �����
        if ($is_html)
        {
            $_callback_type = 'strip_tags';
            $s2 = preg_replace_callback($re_tags, __FUNCTION__, $s2);
            $_callback_type = false;
        }

        if ($s === $s2) break;
        $s = $s2; $i++;
    }#while
    if ($i >= $max) $s = strip_tags($s); #too many cycles for replace...

    if ($is_format_spaces || $is_html)
    {
        #�������� ����������� �������
        $s = preg_replace('/\x20\x20+/s', ' ', trim($s));
        #�������� ������� � ������ � � ����� �����
        $s = str_replace(array("\r\n\x20", "\x20\r\n"), "\r\n", $s);
        #�������� 2 � ����� ��������� ����� �� 2 �������� �����
        $s = preg_replace('/\r\n[\r\n]+/s', "\r\n\r\n", $s);
    }
    return $s;
}


?>