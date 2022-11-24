<?php if(!function_exists("json_encode")) exit("JSON Functions is not available!");
/*
* @package    simple_restserver
* @category   webservice
* @author     Jeff Afable <jeffafable@gmail.com>
* @version    1.0
*/
class simple_restserver {
	public function SetClass($classname){
		$this->ClassName = $classname;
		if(class_exists($this->ClassName)==FALSE){
			$this->Output("CLASS_ERROR: Service Class declared stdClass::{$classname} not exists!");
			exit();
		}
		return $this;
	}
	public function SetAuth($auths = array()){
		foreach($auths as $auth){
			$acc[] = implode("::",$auth);
		}
		$this->ServiceAuth = $acc;
		return $this;
	}
	public function ClassResponse(){
		if(isset($_POST["BAS_REST_FUNC"]) && isset($this->ClassName) && $this->ClassName!="" && $this->ClassName!=NULL){
			$class_name = strip_tags($_POST["BAS_REST_CLASS"]);
			$auth_login = strip_tags($_POST["BAS_REST_AUTH"]);
			if($class_name == $this->ClassName){
				if(isset($this->ServiceAuth) && is_array($this->ServiceAuth) && !in_array($auth_login,$this->ServiceAuth)){
					$this->Output("AUTH_ERROR: Invalid Authentication {\"".str_replace("::","\"@\"",$auth_login)."\"}.");
					exit();
				}
				$function_name = strip_tags($_POST["BAS_REST_FUNC"]);
				$function_vars = @json_decode(strip_tags($_POST["BAS_REST_VARS"]),TRUE);
				if($function_vars==="" || $function_vars===NULL) 
					$function_vars = trim(strip_tags($_POST["BAS_REST_VARS"]),"\"");
				$object = NULL;
				$this->InitClass($object);
				if(method_exists($object,$function_name)){
					if(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4']) && isset($function_vars['var5']) && isset($function_vars['var6']) && isset($function_vars['var7']) && isset($function_vars['var8']) && isset($function_vars['var9']) && isset($function_vars['var10']) && isset($function_vars['var11']) && isset($function_vars['var12']) && isset($function_vars['var13']) && isset($function_vars['var14']) && isset($function_vars['var15'])){
						$return = $object->{$function_name}($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4'],$function_vars['var5'],$function_vars['var6'],$function_vars['var7'],$function_vars['var8'],$function_vars['var9'],$function_vars['var10'],$function_vars['var11'],$function_vars['var12'],$function_vars['var13'],$function_vars['var14'],$function_vars['var15']);
					}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4']) && isset($function_vars['var5']) && isset($function_vars['var6']) && isset($function_vars['var7']) && isset($function_vars['var8']) && isset($function_vars['var9']) && isset($function_vars['var10']) && isset($function_vars['var11']) && isset($function_vars['var12']) && isset($function_vars['var13']) && isset($function_vars['var14'])){
						$return = $object->{$function_name}($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4'],$function_vars['var5'],$function_vars['var6'],$function_vars['var7'],$function_vars['var8'],$function_vars['var9'],$function_vars['var10'],$function_vars['var11'],$function_vars['var12'],$function_vars['var13'],$function_vars['var14']);
					}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4']) && isset($function_vars['var5']) && isset($function_vars['var6']) && isset($function_vars['var7']) && isset($function_vars['var8']) && isset($function_vars['var9']) && isset($function_vars['var10']) && isset($function_vars['var11']) && isset($function_vars['var12']) && isset($function_vars['var13'])){
						$return = $object->{$function_name}($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4'],$function_vars['var5'],$function_vars['var6'],$function_vars['var7'],$function_vars['var8'],$function_vars['var9'],$function_vars['var10'],$function_vars['var11'],$function_vars['var12'],$function_vars['var13']);
					}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4']) && isset($function_vars['var5']) && isset($function_vars['var6']) && isset($function_vars['var7']) && isset($function_vars['var8']) && isset($function_vars['var9']) && isset($function_vars['var10']) && isset($function_vars['var11']) && isset($function_vars['var12'])){
						$return = $object->{$function_name}($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4'],$function_vars['var5'],$function_vars['var6'],$function_vars['var7'],$function_vars['var8'],$function_vars['var9'],$function_vars['var10'],$function_vars['var11'],$function_vars['var12']);
					}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4']) && isset($function_vars['var5']) && isset($function_vars['var6']) && isset($function_vars['var7']) && isset($function_vars['var8']) && isset($function_vars['var9']) && isset($function_vars['var10']) && isset($function_vars['var11'])){
						$return = $object->{$function_name}($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4'],$function_vars['var5'],$function_vars['var6'],$function_vars['var7'],$function_vars['var8'],$function_vars['var9'],$function_vars['var10'],$function_vars['var11']);
					}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4']) && isset($function_vars['var5']) && isset($function_vars['var6']) && isset($function_vars['var7']) && isset($function_vars['var8']) && isset($function_vars['var9']) && isset($function_vars['var10'])){
						$return = $object->{$function_name}($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4'],$function_vars['var5'],$function_vars['var6'],$function_vars['var7'],$function_vars['var8'],$function_vars['var9'],$function_vars['var10']);
					}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4']) && isset($function_vars['var5']) && isset($function_vars['var6']) && isset($function_vars['var7']) && isset($function_vars['var8']) && isset($function_vars['var9'])){
						$return = $object->{$function_name}($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4'],$function_vars['var5'],$function_vars['var6'],$function_vars['var7'],$function_vars['var8'],$function_vars['var9']);
					}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4']) && isset($function_vars['var5']) && isset($function_vars['var6']) && isset($function_vars['var7']) && isset($function_vars['var8'])){
						$return = $object->{$function_name}($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4'],$function_vars['var5'],$function_vars['var6'],$function_vars['var7'],$function_vars['var8']);
					}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4']) && isset($function_vars['var5']) && isset($function_vars['var6']) && isset($function_vars['var7'])){
						$return = $object->{$function_name}($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4'],$function_vars['var5'],$function_vars['var6'],$function_vars['var7']);
					}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4']) && isset($function_vars['var5']) && isset($function_vars['var6'])){
						$return = $object->{$function_name}($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4'],$function_vars['var5'],$function_vars['var6']);
					}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4']) && isset($function_vars['var5'])){
						$return = $object->{$function_name}($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4'],$function_vars['var5']);
					}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4'])){
						$return = $object->{$function_name}($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4']);
					}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3'])){
						$return = $object->{$function_name}($function_vars['var1'],$function_vars['var2'],$function_vars['var3']);
					}elseif(isset($function_vars['var1']) && isset($function_vars['var2'])){
						$return = $object->{$function_name}($function_vars['var1'],$function_vars['var2']);
					}elseif(isset($function_vars['var1'])){
						$return = $object->{$function_name}($function_vars['var1']);
					}else{
						$return = $object->{$function_name}($function_vars);
					}
					$this->Output($return);
				}else{
					$this->Output("METHOD_ERROR: Undefined Method Call {$class_name}::$function_name().");
				}
			}else{
				$this->Output("CLASS_ERROR: Invalid Class Call stdClass::{$class_name}!");
			}
		}elseif(!isset($_POST["BAS_REST_FUNC"]) && (isset($_GET["xml"]) || isset($_GET["info"]))){
			$this->ServiceXML();
		}elseif(!isset($_POST["BAS_REST_FUNC"]) && isset($_GET["class"])){
			print class_exists($_GET["class"]);
		}
		return $this;
	}
	public function SetFunctionList($list){
		$this->Service->Functions = $list;
		return $this;
	}
	public function FunctionResponse(){
		if(isset($_POST["BAS_REST_FUNC"]) && (isset($_POST["BAS_REST_CLASS"]) && $_POST["BAS_REST_CLASS"] == "")){
			$auth_login = strip_tags($_POST["BAS_REST_AUTH"]);
			if(isset($this->ServiceAuth) && is_array($this->ServiceAuth) && !in_array($auth_login,$this->ServiceAuth)){
				$this->Output("AUTH_ERROR: Invalid Authentication {\"".str_replace("::","\"@\"",$auth_login)."\"}.");
				exit();
			}
			$function_name = strip_tags($_POST["BAS_REST_FUNC"]);
			$function_vars = @json_decode(strip_tags($_POST["BAS_REST_VARS"]),TRUE);
			if($function_vars==="" || $function_vars===NULL) $function_vars = trim(strip_tags($_POST["BAS_REST_VARS"]),"\"");
			if(function_exists($function_name) && !isset($this->Service->Functions) && (isset($this->Service->Option->AllFunctions) && $this->Service->Option->AllFunctions==TRUE)){
				if(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4']) && isset($function_vars['var5']) && isset($function_vars['var6']) && isset($function_vars['var7']) && isset($function_vars['var8']) && isset($function_vars['var9']) && isset($function_vars['var10']) && isset($function_vars['var11']) && isset($function_vars['var12']) && isset($function_vars['var13']) && isset($function_vars['var14']) && isset($function_vars['var15'])){
					$return = $function_name($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4'],$function_vars['var5'],$function_vars['var6'],$function_vars['var7'],$function_vars['var8'],$function_vars['var9'],$function_vars['var10'],$function_vars['var11'],$function_vars['var12'],$function_vars['var13'],$function_vars['var14'],$function_vars['var15']);
				}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4']) && isset($function_vars['var5']) && isset($function_vars['var6']) && isset($function_vars['var7']) && isset($function_vars['var8']) && isset($function_vars['var9']) && isset($function_vars['var10']) && isset($function_vars['var11']) && isset($function_vars['var12']) && isset($function_vars['var13']) && isset($function_vars['var14'])){
					$return = $function_name($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4'],$function_vars['var5'],$function_vars['var6'],$function_vars['var7'],$function_vars['var8'],$function_vars['var9'],$function_vars['var10'],$function_vars['var11'],$function_vars['var12'],$function_vars['var13'],$function_vars['var14']);
				}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4']) && isset($function_vars['var5']) && isset($function_vars['var6']) && isset($function_vars['var7']) && isset($function_vars['var8']) && isset($function_vars['var9']) && isset($function_vars['var10']) && isset($function_vars['var11']) && isset($function_vars['var12']) && isset($function_vars['var13'])){
					$return = $function_name($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4'],$function_vars['var5'],$function_vars['var6'],$function_vars['var7'],$function_vars['var8'],$function_vars['var9'],$function_vars['var10'],$function_vars['var11'],$function_vars['var12'],$function_vars['var13']);
				}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4']) && isset($function_vars['var5']) && isset($function_vars['var6']) && isset($function_vars['var7']) && isset($function_vars['var8']) && isset($function_vars['var9']) && isset($function_vars['var10']) && isset($function_vars['var11']) && isset($function_vars['var12'])){
					$return = $function_name($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4'],$function_vars['var5'],$function_vars['var6'],$function_vars['var7'],$function_vars['var8'],$function_vars['var9'],$function_vars['var10'],$function_vars['var11'],$function_vars['var12']);
				}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4']) && isset($function_vars['var5']) && isset($function_vars['var6']) && isset($function_vars['var7']) && isset($function_vars['var8']) && isset($function_vars['var9']) && isset($function_vars['var10']) && isset($function_vars['var11'])){
					$return = $function_name($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4'],$function_vars['var5'],$function_vars['var6'],$function_vars['var7'],$function_vars['var8'],$function_vars['var9'],$function_vars['var10'],$function_vars['var11']);
				}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4']) && isset($function_vars['var5']) && isset($function_vars['var6']) && isset($function_vars['var7']) && isset($function_vars['var8']) && isset($function_vars['var9']) && isset($function_vars['var10'])){
					$return = $function_name($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4'],$function_vars['var5'],$function_vars['var6'],$function_vars['var7'],$function_vars['var8'],$function_vars['var9'],$function_vars['var10']);
				}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4']) && isset($function_vars['var5']) && isset($function_vars['var6']) && isset($function_vars['var7']) && isset($function_vars['var8']) && isset($function_vars['var9'])){
					$return = $function_name($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4'],$function_vars['var5'],$function_vars['var6'],$function_vars['var7'],$function_vars['var8'],$function_vars['var9']);
				}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4']) && isset($function_vars['var5']) && isset($function_vars['var6']) && isset($function_vars['var7']) && isset($function_vars['var8'])){
					$return = $function_name($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4'],$function_vars['var5'],$function_vars['var6'],$function_vars['var7'],$function_vars['var8']);
				}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4']) && isset($function_vars['var5']) && isset($function_vars['var6']) && isset($function_vars['var7'])){
					$return = $function_name($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4'],$function_vars['var5'],$function_vars['var6'],$function_vars['var7']);
				}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4']) && isset($function_vars['var5']) && isset($function_vars['var6'])){
					$return = $function_name($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4'],$function_vars['var5'],$function_vars['var6']);
				}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4']) && isset($function_vars['var5'])){
					$return = $function_name($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4'],$function_vars['var5']);
				}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4'])){
					$return = $function_name($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4']);
				}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3'])){
					$return = $function_name($function_vars['var1'],$function_vars['var2'],$function_vars['var3']);
				}elseif(isset($function_vars['var1']) && isset($function_vars['var2'])){
					$return = $function_name($function_vars['var1'],$function_vars['var2']);
				}elseif(isset($function_vars['var1'])){
					$return = $function_name($function_vars['var1']);
				}else{
					$return = $function_name($function_vars);
				}
				$this->Output($return);
			}elseif(function_exists($function_name) && isset($this->Service->Functions) && is_array($this->Service->Functions) && in_array($function_name,$this->Service->Functions)){
				if(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4']) && isset($function_vars['var5']) && isset($function_vars['var6']) && isset($function_vars['var7']) && isset($function_vars['var8']) && isset($function_vars['var9']) && isset($function_vars['var10']) && isset($function_vars['var11']) && isset($function_vars['var12']) && isset($function_vars['var13']) && isset($function_vars['var14']) && isset($function_vars['var15'])){
					$return = $function_name($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4'],$function_vars['var5'],$function_vars['var6'],$function_vars['var7'],$function_vars['var8'],$function_vars['var9'],$function_vars['var10'],$function_vars['var11'],$function_vars['var12'],$function_vars['var13'],$function_vars['var14'],$function_vars['var15']);
				}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4']) && isset($function_vars['var5']) && isset($function_vars['var6']) && isset($function_vars['var7']) && isset($function_vars['var8']) && isset($function_vars['var9']) && isset($function_vars['var10']) && isset($function_vars['var11']) && isset($function_vars['var12']) && isset($function_vars['var13']) && isset($function_vars['var14'])){
					$return = $function_name($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4'],$function_vars['var5'],$function_vars['var6'],$function_vars['var7'],$function_vars['var8'],$function_vars['var9'],$function_vars['var10'],$function_vars['var11'],$function_vars['var12'],$function_vars['var13'],$function_vars['var14']);
				}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4']) && isset($function_vars['var5']) && isset($function_vars['var6']) && isset($function_vars['var7']) && isset($function_vars['var8']) && isset($function_vars['var9']) && isset($function_vars['var10']) && isset($function_vars['var11']) && isset($function_vars['var12']) && isset($function_vars['var13'])){
					$return = $function_name($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4'],$function_vars['var5'],$function_vars['var6'],$function_vars['var7'],$function_vars['var8'],$function_vars['var9'],$function_vars['var10'],$function_vars['var11'],$function_vars['var12'],$function_vars['var13']);
				}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4']) && isset($function_vars['var5']) && isset($function_vars['var6']) && isset($function_vars['var7']) && isset($function_vars['var8']) && isset($function_vars['var9']) && isset($function_vars['var10']) && isset($function_vars['var11']) && isset($function_vars['var12'])){
					$return = $function_name($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4'],$function_vars['var5'],$function_vars['var6'],$function_vars['var7'],$function_vars['var8'],$function_vars['var9'],$function_vars['var10'],$function_vars['var11'],$function_vars['var12']);
				}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4']) && isset($function_vars['var5']) && isset($function_vars['var6']) && isset($function_vars['var7']) && isset($function_vars['var8']) && isset($function_vars['var9']) && isset($function_vars['var10']) && isset($function_vars['var11'])){
					$return = $function_name($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4'],$function_vars['var5'],$function_vars['var6'],$function_vars['var7'],$function_vars['var8'],$function_vars['var9'],$function_vars['var10'],$function_vars['var11']);
				}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4']) && isset($function_vars['var5']) && isset($function_vars['var6']) && isset($function_vars['var7']) && isset($function_vars['var8']) && isset($function_vars['var9']) && isset($function_vars['var10'])){
					$return = $function_name($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4'],$function_vars['var5'],$function_vars['var6'],$function_vars['var7'],$function_vars['var8'],$function_vars['var9'],$function_vars['var10']);
				}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4']) && isset($function_vars['var5']) && isset($function_vars['var6']) && isset($function_vars['var7']) && isset($function_vars['var8']) && isset($function_vars['var9'])){
					$return = $function_name($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4'],$function_vars['var5'],$function_vars['var6'],$function_vars['var7'],$function_vars['var8'],$function_vars['var9']);
				}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4']) && isset($function_vars['var5']) && isset($function_vars['var6']) && isset($function_vars['var7']) && isset($function_vars['var8'])){
					$return = $function_name($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4'],$function_vars['var5'],$function_vars['var6'],$function_vars['var7'],$function_vars['var8']);
				}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4']) && isset($function_vars['var5']) && isset($function_vars['var6']) && isset($function_vars['var7'])){
					$return = $function_name($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4'],$function_vars['var5'],$function_vars['var6'],$function_vars['var7']);
				}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4']) && isset($function_vars['var5']) && isset($function_vars['var6'])){
					$return = $function_name($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4'],$function_vars['var5'],$function_vars['var6']);
				}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4']) && isset($function_vars['var5'])){
					$return = $function_name($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4'],$function_vars['var5']);
				}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3']) && isset($function_vars['var4'])){
					$return = $function_name($function_vars['var1'],$function_vars['var2'],$function_vars['var3'],$function_vars['var4']);
				}elseif(isset($function_vars['var1']) && isset($function_vars['var2']) && isset($function_vars['var3'])){
					$return = $function_name($function_vars['var1'],$function_vars['var2'],$function_vars['var3']);
				}elseif(isset($function_vars['var1']) && isset($function_vars['var2'])){
					$return = $function_name($function_vars['var1'],$function_vars['var2']);
				}elseif(isset($function_vars['var1'])){
					$return = $function_name($function_vars['var1']);
				}else{
					$return = $function_name($function_vars);
				}
				$this->Output($return);
			}else{
				$this->Output("METHOD_ERROR: Undefined Function Call $function_name().");
			}
		}elseif(!isset($_POST["BAS_REST_FUNC"]) && (isset($_GET["xml"]) || isset($_GET["info"]))){
			$this->ServiceXML();
		}
		return $this;
	}
	private function InitClass(&$class=NULL){
		if(!isset($_SESSION[$this->ClassName])){
			$this->{$this->ClassName} =new $this->ClassName();
		}else{
			$this->{$this->ClassName} = $_SESSION[$this->ClassName];
		}
		$class = $this->{$this->ClassName};	
		return $this;
	}
	private function Output($in){
		$in = json_encode(array($in));
		print json_encode(array("Result"=>$in));
		return $this;
	}
	private function ServiceXML(){
		$path_info = GetUrlPath();
		$auth_info = (isset($this->ServiceAuth) && is_array($this->ServiceAuth) && sizeof($this->ServiceAuth)>0) ? " AUTHENTICATION=\"TRUE\"" : " AUTHENTICATION=\"FALSE\"";
		header("Content-type: XML");
		print "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
		print "<!--";
		if(isset($this->Comments)){
			print $this->Comments;
		}else{
			print "\n   REST WebService\n";
			print "   XML Information\n";
		}
		print "-->\n";
		print "<SERVICE URL=\"".trim(Controller::AppBaseUrl(),"/")."$path_info\"{$auth_info}>\n";
		if(isset($this->ClassName)){
			$class =new $this->ClassName();
			$name 		= get_class($class);
			$methods	= get_class_methods($class);
			$class_var	= get_object_vars($class);
			print "  <CLASS NAME=\"{$name}\">\n";
			print "    <VARIABLES>\n";
			foreach($class_var as $key => $val){
				if(is_float($val)){
					$type = "Float";
				}elseif(is_bool($val)){
					$type = "Boolean";
					$val = ($val==TRUE) ? "TRUE" : "FALSE";
				}elseif(is_numeric($val)){
					$type = "Integer";
				}elseif(is_array($val)){
					$type = "Array";
					$val = json_encode($val);
					$val = str_replace("\"","'",$val);
				}elseif(is_object($val)){
					$type = "Object";
					$val = json_encode($val);
					$val = str_replace("\"","'",$val);
				}else{
					$type = "String";
				}
				print "      <VARIABLE NAME=\"{$key}\" TYPE=\"{$type}\" />\n";
			}
			print "    </VARIABLES>\n";
			print "    <METHODS>\n";
			foreach($methods as $key => $funcs){
				print "      <METHOD NAME=\"{$funcs}\"/>\n";
			}
			print "    </METHODS>\n";
			print "  </CLASS>\n";
		}elseif(isset($this->Service->Functions) && is_array($this->Service->Functions)){
			print "  <FUNCTIONS>\n";
			foreach($this->Service->Functions as $func_name){
				print "  <FUNCTION NAME=\"{$func_name}\"/>\n";
			}
			print "  </FUNCTIONS>\n";
		}elseif(!isset($this->Service->Functions) && (isset($this->Service->Option->AllFunctions) && $this->Service->Option->AllFunctions==TRUE)){
			print "<!-- ALL SERVER FUNCTIONS CAN BE ACCESS VIA REST WEBSERVICE -->\n";
		}else{
			print "<!-- SERVER HAS NO FUNCTION DECLARED -->\n";
		}
		print "</SERVICE>";
		return $this;
	}
}
# END_PHP_FILE
