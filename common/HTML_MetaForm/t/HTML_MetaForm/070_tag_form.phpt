--TEST--
HTML_MetaForm: tags meta-information
--FILE--
<?php
require dirname(__FILE__) . '/init.php';
ob_start();
?>
<form method="post" action="abc" meta:validator="val">
  <script>
    // Must be totally ignored.
    document.write("<input type=text name=txt1 value='123'/> - must be empty!<br>");
  </script>
  
  <input type=text name=txt1 default="1.1" value="" meta:a="b"><br/>
  <textarea name="textarea" default=""></textarea><br/>
  
  <input type="checkbox" name="a[]" value="0" label="0^" meta:validator="group_filled"/><br/>
  <input type="checkbox" name="a[]" value="1" label="1^" meta:validator="group_filled_override"/><br/>
  <input type="checkbox" name="a[]" value="2" label="2^" /><br/>
  
  <input type="checkbox" name="n[0]" label="n0^" /><br/>
  <input type="checkbox" name="n[1]" label="n1^" /><br/>
  
  <input type="checkbox" name="bad[]" label="bad^" /><br/>
  
  <input type=text name=txt1 default="1.1"  meta:c="d"/>
  <input type=text name=txt2[b] default="3.3">
  <input type=text name=txt2[] default="2.1">
  <input type=text name=txt2[] default="2.2">
  <input type=text name=txt3[a][] default="3.1">
  <input type=text name=txt3[a][] default="3.2">
  <input type=text name="txt4" default="4.1">

  <br>
  <textarea name=area1 default="ssss"></textarea>
  <textarea name=area2[]></textarea>
  <textarea name=area2[]></textarea>

  <br>
  <input type=radio name=rad1 value=r id="r1a"><label for="r1a">right</label> |
  <input type=radio name=rad1 value=l label="left^"> |
  <input type=radio name=rad1 value=u> |
  <input type=radio name=rad1 value=d> |||
  <input type=radio name=rad2[a] value=u default=u> |
  <input type=radio name=rad2[a] value=d> |||
  <input type=radio name="rad" value="a\'s" label="Test of"> 
  <input type=radio name="rad" value="b\'s" label="apostfophs^"> |

  <br>
  <input type=checkbox name=chk1[] value=aaa default>
  <input type=checkbox name=chk1[] value=bbb> |||
  <input type=checkbox name=chk2[a] value=xxx>
  <input type=checkbox name=chk2[b] value=yyy> |||

  <br>
  <select name=sel-1>
    options[some][key]
  </select>
  <select name=sel0>
    <option value="0">0000</option>
    $options[some][key]
    <option value="1">1111</option>
  </select>
  <select name=sel1>
    <optgroup label="aaa">
      <option value="0">rrrrr</option>
      options[some][key]
    </optgroup>
    options[other][key]
  </select>
  <select name=sel2 size="1">
    <optgroup label="First">
      <option value=a>aaaaaaaaaaaaa
      <option value=b>bbbbbbbbbbbbb
    </optgroup>
    <optgroup label="Second">
      <option value=c>ccccccccccccc
    </optgroup>
  </select>
  <select name=sel3 size="3" bb="eaaa">
    <option value=a>aaaaaaaaaaaaa
    <option value=b selected>bbbbbbbbbbbbb
    <option value=c>ccccccccccccc
  </select>
  <select name=sel4 multiple size="3">
    <option value=a>aaaaaaaaaaaaa
    <option value=b>bbbbbbbbbbbbb
    <option value=c>ccccccccccccc
  </select>

  <br>
  <input type=submit>
  <input type=submit confirm="Are you sure?">
</form>
<?php
printr(_getMeta($MetaForm->process(ob_get_clean())));
?>



--EXPECT--
array (
  'validator' => 'val',
  'original' => 'abc',
  'name' => NULL,
  'type' => 'form',
  'id' => NULL,
  'items' => 
  array (
    'txt1' => 
    array (
      'c' => 'd',
      'a' => 'b',
      'type' => 'text',
      'name' => 'txt1',
      'value' => NULL,
    ),
    'textarea' => 
    array (
      'type' => 'text',
      'name' => 'textarea',
      'value' => NULL,
    ),
    'a[]' => 
    array (
      'validator' => 'group_filled_override',
      'type' => 'multiple',
      'items' => 
      array (
        0 => NULL,
        1 => NULL,
        2 => NULL,
      ),
      'name' => 'a',
      'value' => 
      array (
      ),
    ),
    'n[0]' => 
    array (
      'type' => 'flag',
      'name' => 'n[0]',
      'value' => 0,
    ),
    'n[1]' => 
    array (
      'type' => 'flag',
      'name' => 'n[1]',
      'value' => 0,
    ),
    'bad[]' => 
    array (
      'type' => 'multiple',
      'items' => 
      array (
        'on' => NULL,
      ),
      'name' => 'bad',
      'value' => 
      array (
      ),
    ),
    'txt2[b]' => 
    array (
      'type' => 'text',
      'name' => 'txt2[b]',
      'value' => NULL,
    ),
    'txt2[]' => 
    array (
      'type' => 'text',
      'name' => 'txt2[]',
      'value' => NULL,
    ),
    'txt3[a][]' => 
    array (
      'type' => 'text',
      'name' => 'txt3[a][]',
      'value' => NULL,
    ),
    'txt4' => 
    array (
      'type' => 'text',
      'name' => 'txt4',
      'value' => NULL,
    ),
    'area1' => 
    array (
      'type' => 'text',
      'name' => 'area1',
      'value' => NULL,
    ),
    'area2[]' => 
    array (
      'type' => 'text',
      'name' => 'area2[]',
      'value' => NULL,
    ),
    'rad1' => 
    array (
      'type' => 'single',
      'id' => 'r1a',
      'items' => 
      array (
        'r' => 'right',
        'l' => NULL,
        'u' => NULL,
        'd' => NULL,
      ),
      'name' => 'rad1',
      'label' => 'right',
      'value' => NULL,
    ),
    'rad2[a]' => 
    array (
      'type' => 'single',
      'items' => 
      array (
        'u' => NULL,
        'd' => NULL,
      ),
      'name' => 'rad2[a]',
      'value' => NULL,
    ),
    'rad' => 
    array (
      'type' => 'single',
      'items' => 
      array (
        'a\\\'s' => NULL,
        'b\\\'s' => NULL,
      ),
      'name' => 'rad',
      'value' => NULL,
    ),
    'chk1[]' => 
    array (
      'type' => 'multiple',
      'items' => 
      array (
        'aaa' => NULL,
        'bbb' => NULL,
      ),
      'name' => 'chk1',
      'value' => 
      array (
      ),
    ),
    'chk2[a]' => 
    array (
      'type' => 'flag',
      'name' => 'chk2[a]',
      'value' => 0,
    ),
    'chk2[b]' => 
    array (
      'type' => 'flag',
      'name' => 'chk2[b]',
      'value' => 0,
    ),
    'sel0' => 
    array (
      'type' => 'single',
      'items' => 
      array (
        0 => '0000',
        1 => '1111',
      ),
      'name' => 'sel0',
      'value' => NULL,
    ),
    'sel1' => 
    array (
      'type' => 'single',
      'items' => 
      array (
        0 => 'rrrrr',
      ),
      'name' => 'sel1',
      'value' => NULL,
    ),
    'sel2' => 
    array (
      'type' => 'single',
      'items' => 
      array (
        'a' => 'aaaaaaaaaaaaa',
        'b' => 'bbbbbbbbbbbbb',
        'c' => 'ccccccccccccc',
      ),
      'name' => 'sel2',
      'value' => NULL,
    ),
    'sel3' => 
    array (
      'type' => 'single',
      'items' => 
      array (
        'a' => 'aaaaaaaaaaaaa',
        'b' => 'bbbbbbbbbbbbb',
        'c' => 'ccccccccccccc',
      ),
      'name' => 'sel3',
      'value' => NULL,
    ),
    'sel4' => 
    array (
      'type' => 'flag',
      'name' => 'sel4',
      'label' => 'ccccccccccccc',
      'value' => 0,
    ),
  ),
  'tree' => 
  array (
    'txt1' => 
    array (
      'c' => 'd',
      'a' => 'b',
      'type' => 'text',
      'name' => 'txt1',
      'value' => NULL,
    ),
    'textarea' => 
    array (
      'type' => 'text',
      'name' => 'textarea',
      'value' => NULL,
    ),
    'a' => 
    array (
      'validator' => 'group_filled_override',
      'type' => 'multiple',
      'items' => 
      array (
        0 => NULL,
        1 => NULL,
        2 => NULL,
      ),
      'name' => 'a',
      'value' => 
      array (
      ),
    ),
    'n' => 
    array (
      0 => 
      array (
        'type' => 'flag',
        'name' => 'n[0]',
        'value' => 0,
      ),
      1 => 
      array (
        'type' => 'flag',
        'name' => 'n[1]',
        'value' => 0,
      ),
    ),
    'bad' => 
    array (
      'type' => 'multiple',
      'items' => 
      array (
        'on' => NULL,
      ),
      'name' => 'bad',
      'value' => 
      array (
      ),
    ),
    'txt2' => 
    array (
      'type' => 'text',
      'name' => 'txt2[]',
      'value' => NULL,
    ),
    'txt3' => 
    array (
      'a' => 
      array (
        'type' => 'text',
        'name' => 'txt3[a][]',
        'value' => NULL,
      ),
    ),
    'txt4' => 
    array (
      'type' => 'text',
      'name' => 'txt4',
      'value' => NULL,
    ),
    'area1' => 
    array (
      'type' => 'text',
      'name' => 'area1',
      'value' => NULL,
    ),
    'area2' => 
    array (
      'type' => 'text',
      'name' => 'area2[]',
      'value' => NULL,
    ),
    'rad1' => 
    array (
      'type' => 'single',
      'id' => 'r1a',
      'items' => 
      array (
        'r' => 'right',
        'l' => NULL,
        'u' => NULL,
        'd' => NULL,
      ),
      'name' => 'rad1',
      'label' => 'right',
      'value' => NULL,
    ),
    'rad2' => 
    array (
      'a' => 
      array (
        'type' => 'single',
        'items' => 
        array (
          'u' => NULL,
          'd' => NULL,
        ),
        'name' => 'rad2[a]',
        'value' => NULL,
      ),
    ),
    'rad' => 
    array (
      'type' => 'single',
      'items' => 
      array (
        'a\\\'s' => NULL,
        'b\\\'s' => NULL,
      ),
      'name' => 'rad',
      'value' => NULL,
    ),
    'chk1' => 
    array (
      'type' => 'multiple',
      'items' => 
      array (
        'aaa' => NULL,
        'bbb' => NULL,
      ),
      'name' => 'chk1',
      'value' => 
      array (
      ),
    ),
    'chk2' => 
    array (
      'a' => 
      array (
        'type' => 'flag',
        'name' => 'chk2[a]',
        'value' => 0,
      ),
      'b' => 
      array (
        'type' => 'flag',
        'name' => 'chk2[b]',
        'value' => 0,
      ),
    ),
    'sel0' => 
    array (
      'type' => 'single',
      'items' => 
      array (
        0 => '0000',
        1 => '1111',
      ),
      'name' => 'sel0',
      'value' => NULL,
    ),
    'sel1' => 
    array (
      'type' => 'single',
      'items' => 
      array (
        0 => 'rrrrr',
      ),
      'name' => 'sel1',
      'value' => NULL,
    ),
    'sel2' => 
    array (
      'type' => 'single',
      'items' => 
      array (
        'a' => 'aaaaaaaaaaaaa',
        'b' => 'bbbbbbbbbbbbb',
        'c' => 'ccccccccccccc',
      ),
      'name' => 'sel2',
      'value' => NULL,
    ),
    'sel3' => 
    array (
      'type' => 'single',
      'items' => 
      array (
        'a' => 'aaaaaaaaaaaaa',
        'b' => 'bbbbbbbbbbbbb',
        'c' => 'ccccccccccccc',
      ),
      'name' => 'sel3',
      'value' => NULL,
    ),
    'sel4' => 
    array (
      'type' => 'flag',
      'name' => 'sel4',
      'label' => 'ccccccccccccc',
      'value' => 0,
    ),
  ),
  'value' => 
  array (
    'txt1' => NULL,
    'textarea' => NULL,
    'a' => 
    array (
    ),
    'n' => 
    array (
      0 => 0,
      1 => 0,
    ),
    'bad' => 
    array (
    ),
    'txt2' => 
    array (
      'b' => NULL,
      '' => NULL,
    ),
    'txt3' => 
    array (
      'a' => 
      array (
        '' => NULL,
      ),
    ),
    'txt4' => NULL,
    'area1' => NULL,
    'area2' => 
    array (
      '' => NULL,
    ),
    'rad1' => NULL,
    'rad2' => 
    array (
      'a' => NULL,
    ),
    'rad' => NULL,
    'chk1' => 
    array (
    ),
    'chk2' => 
    array (
      'a' => 0,
      'b' => 0,
    ),
    'sel0' => NULL,
    'sel1' => NULL,
    'sel2' => NULL,
    'sel3' => NULL,
    'sel4' => 0,
  ),
)
