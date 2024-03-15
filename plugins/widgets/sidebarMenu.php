<?php
if(!defined('ROOT')) exit('No direct script access allowed');

loadModuleLib("navigator","api");

$menuTree1=generateNavigationFromDB(getConfig("DEFAULT_NAVIGATION"),"links","core");

$menuTree2=generateNavigationFromDir(APPROOT."misc/menus/cms/","core");

if(CMS_SITENAME!="cms") {
  $menuTree3=generateNavigationFromDir(CMS_APPROOT."misc/menus/cms/","core");
} else {
  $menuTree3=[];
}

$menuTree=array_merge_recursive($menuTree1,$menuTree2);
$menuTree=array_merge_recursive($menuTree,$menuTree3);

$generalGroup = [];
foreach($menuTree as $a=>$group) {
    foreach($group as $b=>$menu) {
        $menuTree[$a][$b]['menugroup'] = $a;
        $generalGroup[] = $menuTree[$a][$b];
    }
}
usort($generalGroup, "sortMenuByWeight");

function sortMenuByWeight($a, $b) {
    if($a['weight'] == $b['weight']) return 0;
    return ($a['weight'] < $b['weight']) ? -1 : 1;
}
$finalMenuTree = [];
foreach($generalGroup as $a=>$b) {
    if(!isset($finalMenuTree[$b['menugroup']])) $finalMenuTree[$b['menugroup']] = [];
    $finalMenuTree[$b['menugroup']][] = $b;
}
$menuTree = $finalMenuTree;
?>
<style>
.sidebarMenu {
  width: 100%;
  overflow-x: auto;
  position: absolute;
  bottom: -20px;
  top: 32px;
  left: 0px;
  right: 0px;
}
</style>
<div id='sidebarMenu' class="panel-group sidebarMenu" role="tablist" aria-multiselectable="true">
  <?php
    foreach ($menuTree as $category=>$menuSet) {
      if(count($menuSet)<=0) continue;
      $hash=md5("sidebarMenu".$category);
      echo "<div class='panel panel-default'>";
      echo "  <div class='panel-heading' role='tab' id='$hash'>";
      echo "    <h4 class='panel-title'>";
      echo "      <a role='button' data-toggle='collapse' data-parent='#sidebarMenu' href='#collapse{$hash}' aria-expanded='true' aria-controls='collapseOne'>";
      echo "        $category";
      echo "      </a>";
      echo "      <i class='fa fa-angle-right pull-right'></i>";
      echo "    </h4>";
      echo "  </div>";
      echo "  <div id='collapse{$hash}' class='panel-collapse collapse' role='tabpanel' aria-labelledby='$hash'>";
      echo "    <div class='panel-body'>";
      
      foreach ($menuSet as $key => $menu) {
        $more=[];
        if($menu['target']!=null && strlen($menu['target'])>0) {
          $more[]="target='{$menu['target']}'";
        }
        if($menu['class']!=null && strlen($menu['class'])>0) {
          $more[]="class='menuItem {$menu['class']}'";
        } else {
          $more[]="class='menuItem'";
        }
        if($menu['category']!=null && strlen($menu['category'])>0) {
          $more[]="data-category='{$menu['category']}'";
        }
        if($menu['tips']!=null && strlen($menu['tips'])>0) {
          $more[]="title='{$menu['tips']}'";
        }
        if($menu['iconpath']!=null && strlen($menu['iconpath'])>0) {
          echo "<a href='{$menu['link']}' ".implode(" ", $more)."><i class='menuIcon {$menu['iconpath']}'></i>&nbsp; {$menu['title']}</a>";
        } else {
          echo "<a href='{$menu['link']}' ".implode(" ", $more).">{$menu['title']}</a>";
        }
      }

      echo "    </div>";
      echo "  </div>";
      echo "</div>";
    }
  ?>
</div>
<script>
$(function() {
  $("#sidebarMenu").delegate("a.menuItem[href]","click",function(e) {
        ttl=$(this).text();
        href=$(this).attr("href");

        if($(this).attr("target")==null) {
          e.preventDefault();

          if(href.indexOf("http://")===0 || href.indexOf("https://")===0) {
            openLinkFrame(ttl,href);
          } else {
            openLinkFrame(ttl,_link(href));
          }
        } else {
			window.open(href);
		}
    });
});
</script>
