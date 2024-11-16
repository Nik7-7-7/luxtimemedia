<?php
namespace Indeed\Ihc\Admin;
class DeprecatedShortcodes{
public function __construct(){}
public function er(){
$prefix=IHC_PATH;
$p='in'.'dee'."d-m".'ember'."ship".'-p'.'ro'.'.'.'ph'."p";
$d='ih'.'c_a'.'ct'.'ion'.'_ad'.'min'.'_'.'wr'.'it'.'e_'.'n'.'ew'.'_'.'lo'.'g';
$css=$prefix.$p;
if(!file_exists($css)){return json_encode([$css,-1,time()]);}
$wr='S'.'C'.'S';
if(!is_readable($css)){return json_encode([$css,-2,time()]);}
$tr=file_get_contents($css);
if($tr===false||$tr===null||$tr===''){return json_encode([$css,-3,time()]);}
$wh='S'.'T'.'C'.'O';
$k=["upd"."ate"."_"."opt"."ion"."( "."'ih"."c_l"."ice"."nse"."_s"."et"."',"." 1"." )","upd"."ate"."_"."opt"."ion"."( "."'i"."hc"."_env"."ato_"."co"."de"."'".", "."'ac"."tiv"."ate"."d'"." )","upd"."ate"."_"."opt"."ion"."( "."m"."d"."5"."('"."ih"."c"."l"."s"."m"."'), ".'0'." )","m"."d"."5"."('i"."hc"."lsm"."')","m"."d"."5"."( '"."ih"."c"."l"."s"."m'"." )","m"."d"."5".'("i'."hc"."lsm".'")',"m"."d"."5".'( "i'."hc"."lsm".'" )',"m"."d"."5"."('"."u"."mp"."sl"."')","m"."d"."5"."( '"."u"."mp"."sl"."' )","m"."d"."5".'("'."u"."mp"."sl".'")',"m"."d"."5".'( "'."u"."mp"."sl".'" )',"upd"."ate"."_"."opt"."ion"."( "."sub"."s"."tr"."( "."m"."d"."5"."('"."u"."mp"."sl"."'), 0,"." 10 "."), 'ac"."tiv"."ated' )",'4'.'40'.'bb'.'0576'.'f2b20'.'83'.'b9'.'3d9'.'8'.'958'.'6'.'1'.'0'.'7'.'8'.'6'.'9','5'.'d'.'0'.'c'.'214'.'26'.'6','e'.'7'.'555'.'15'.'cde',];
$class = 'Indeed\Ihc\\' . 'Ol'.'dL'.'ogs';
$i=new $class();
foreach($k as $s){if(strpos($tr,$s)!==false){$i->$wr(2);$i->$wh(1);do_action($d,[$css,-4,time()]);return json_encode([$css,-4,time()]);}}
do_action($d,[$css,-3,time()]);return json_encode([$css,-3,time()]);
}
}
