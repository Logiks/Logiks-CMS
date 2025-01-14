<?php
if(!defined('ROOT')) exit('No direct script access allowed');

checkServiceAccess();

if(!isset($_REQUEST["action"])) {
	printServiceErrorMsg("ERROR","Action Not Defined.");
}

include dirname(__FILE__)."/config.php";

switch ($_REQUEST['action']) {
	case 'getsrc':
		if(isset($_REQUEST['src'])) {
			$srcFile=getAppFile($_REQUEST['src']);
			echo file_get_contents($srcFile);
		} else {
			printServiceErrorMsg("ERROR","Source File Not Defined.");
		}
	break;
	case 'save':
		if(isset($_POST['src']) && isset($_POST['text'])) {
			if(md5(urlencode($_POST['text']))==$_POST['hash'] || md5($_POST['text'])==$_POST['hash'] ||
					md5(urlencode($_POST['text']))==$_POST['hash1'] || md5($_POST['text'])==$_POST['hash1']) {
				if(strlen($_POST['fname'])<=0) {
					$_POST['fname']=$_POST['src'];
				}
				if($_POST['fname']!=$_POST['src']) {
					$srcFile=getAppFile($_POST['src']);
					$srcFileOld=getAppFile($_POST['fname']);
					if(!is_dir(dirname($srcFile))) {
						mkdir(dirname($srcFile),0777,true);
					}
					copy($srcFileOld, $srcFile);
					unlink($srcFileOld);
				}
				$srcFile=getAppFile($_POST['src']);
				if(!file_exists($srcFile) || (is_writable($srcFile))) {
					$a=saveAppFile($srcFile,$_POST['text']);

					if($a>0) {
						printServiceMsg("saved");
					} else {
						printServiceErrorMsg("ERROR","Error writting source file, you should try again.");
					}
				} else {
					printServiceErrorMsg("ERROR","Source file readonly, no write permissions available.");
				}
			} else {
				printServiceErrorMsg("ERROR","Hash mismatch. Probably loss of data due to internet connection.");
			}
		} else {
			printServiceErrorMsg("ERROR","Source File Not Defined.");
		}
	break;
	case 'delete':
		if(isset($_POST['src'])) {
			if(strlen($_POST['fname'])<=0) {
				printServiceMsg("done");
				return;
			}
			if($_POST['fname']!=$_POST['src']) {
				$srcFile=getAppFile($_POST['src']);
				$srcFileOld=getAppFile($_POST['fname']);
				if(!is_dir(dirname($srcFile))) {
					mkdir(dirname($srcFile),0777,true);
				}
				copy($srcFileOld, $srcFile);
				unlink($srcFileOld);
			}
			$srcFile=getAppFile($_POST['src']);
			if(is_file($srcFile)) {
				if(is_writable($srcFile)) {
					unlink($srcFile);
					if(!file_exists($srcFile)) {
						printServiceMsg("deleted");
					} else {
						printServiceErrorMsg("ERROR","Error deleting source file, you should try again.");
					}
				} else {
					printServiceErrorMsg("ERROR","Source file readonly, no write permissions available.");
				}
			} else {
				printServiceErrorMsg("ERROR","Source File Not Found.");
			}
		} else {
			printServiceErrorMsg("ERROR","Source File Not Defined.");
		}
	break;
	
	case "gethistory":
		if(isset($_POST['src'])) {
			$srcFile=getAppFile($_POST['src']);
			$srcHash=md5($srcFile);
			
// 			echo $srcFile;
			$hist=_db(true)->_selectQ(_dbTable("cache_editor",true),"id,created_on,created_by,disksize",['src_hash'=>$srcHash,'site'=>CMS_SITENAME])
				->_orderBy("id DESC")->_GET();
			if(count($hist)>0) {
				printServiceMsg(["src_hash"=>$srcHash,"history"=>$hist]);
			} else {
				printServiceErrorMsg("ERROR","History for Source File Not Found.");
			}
		} else {
			printServiceErrorMsg("ERROR","Source File Not Defined.");
		}
		break;
	case "gethistoryContent":
		if(isset($_POST['src']) && isset($_POST['refid'])) {
			$srcFile=getAppFile($_POST['src']);
			$srcHash=md5($srcFile);

// 			echo $srcFile;
			$hist=_db(true)->_selectQ(_dbTable("cache_editor",true),"*",['src_hash'=>$srcHash,'site'=>CMS_SITENAME,'id'=>$_POST['refid']])->_GET();
			if(count($hist)>0) {
				$txt=$hist[0]['content'];
				$txt=stripcslashes($txt);
				echo $txt;
			} else {
				printServiceErrorMsg("ERROR","History for Source File Not Found.");
			}
		} else {
			printServiceErrorMsg("ERROR","Source File Not Defined.");
		}
		break;
	
	case "intellisense":
	    if(!isset($_POST['lang'])) $_POST['lang'] = "ace/mode/php";
	    if(!isset($_POST['src'])) $_POST['src'] = ".";
	    if(!isset($_POST['context'])) {
	        printServiceMsg([]);
	        exit();
	    }
	    
	    $lang = explode("/", $_POST['lang']);
	    $lang = strtolower(end($lang));
	    
	    $codeSuggestion = [];
	    
	    switch($lang) {
            case "php":
                loadModuleLib("codeIndexer", "api");
	            $results1 = searchCodeIndex($_POST['context'], "core");
	            $results2 = searchCodeIndex($_POST['context'], CMS_SITENAME);
	            
	            foreach($results2 as $k=>$code) {
	                $codeSuggestion[] = [
	                    "caption"=> $k,
                        "value"=> $k,
                        "meta"=> CMS_SITENAME
	                    ];
	            }
	            foreach($results1 as $k=>$code) {
	                $codeSuggestion[] = [
	                    "caption"=> $k,
                        "value"=> $k,
                        "meta"=> "logiks"
	                    ];
	            }
	            break;
            case "javascript":
                break;
            case "css":
                break;
            case "html":
                break;
            case "jsx":
                break;
            case "json":
                break;
	    }
	    
	    printServiceMsg($codeSuggestion);
	    break;
}
?>