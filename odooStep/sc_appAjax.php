<?php
function stepGet($r, $i,$textFilter) {  
    $c = new Criteria();
    $c->addDescendingOrderByColumn('ID');
    $c->setLimit($r);
    $c->setOffset($i);
    if ($textFilter) {
      $c->add(OdooStepStepPeer::NOMBRE,'%'. $textFilter . '%',Criteria::LIKE);
    }
    
    $steps = OdooStepStepPeer::doSelect($c);
    $return = array();
    foreach ($steps as $k => $v) {
      $return[] = array("ID" => $v->getId(), "NAME" => $v->getNombre(), "METODO" => $v->getMethod(), "MODELO" => $v->getModel());
    }
   return (array(count($return), array_slice($return, $i, $r)));
}

try {
  $option = $_POST["option"];

  switch ($option) {
    case "LST": $pageSize = $_POST["pageSize"];

                $limit = isset($_POST["limit"])? $_POST["limit"] : $pageSize;
                $start = isset($_POST["start"])? $_POST["start"] : 0;
                $textFilter = isset($_POST["textFilter"])? $_POST["textFilter"] : "";

                list($userNum, $user) = stepGet($limit, $start,$textFilter);

                //echo "{success: " . true . ", resultTotal: " . count($user) . ", resultRoot: " . G::json_encode($user) . "}";
                echo G::json_encode(array("success" => true, "resultTotal" => $userNum, "resultRoot" => $user));
                break;
    case "NEWSTEP" :
    try {
            $ostep = new OdooStepStep();

            $c = new Criteria();
            $c->addDescendingOrderByColumn('ID');
            $lastStep = OdooStepStepPeer::doSelectOne($c);
            if (! is_null( $lastStep )) {
                $id = $lastStep->getId();
                $stepid = $lastStep->getStepId();
                $contador = substr($stepid, -4);
                $int = (int)$contador;
                $int ++;
                $str = (string)$int;
                $strlen = strlen($str);
                $stepid = substr_replace($stepid, $str, -$strlen);
            } else {
                $stepid = "4553885635943a689c55440011040000";
            }

            $ostep->setStepId($stepid);
            $ostep->setNombre($_POST["newNombre"]);
            $ostep->setProUid($_POST["newProceso"]);
            $ostep->setModel($_POST["newModelo"]);
            $ostep->setMethod($_POST["newMetodo"]);
            $ostep->setOutput($_POST["newSalida"]);
            
            $parametros = preg_split("/[\s,]+/",$_POST["newParametros"]);// Separación v,v,v,...

            preg_match_all("/ ([^:\n]+) : ([^\n]+) /x", $_POST["newParametrosKW"], $p); // Separación k:v,v,v INTRO k:v,....
            $kwparams = array_combine($p[1], $p[2]);
                       
            $ostep->setParameters(serialize($parametros));
            $ostep->setKwParameters(serialize($kwparams));
            $ostep->save();
            //PMPluginRegistry::registerStep( "odooStep", $ostep->getStepId(), "stepodooStepApplication",$ostep->getModel());
            
            //PMPlugin::registerStep($ostep->getStepId(), "stepodooStepApplication",$ostep->getModel());
            $oPluginRegistry = PMPluginRegistry::getSingleton();
            $oPluginRegistry->registerStep( "odooStep", $ostep->getStepId(), "stepodooStepApplication",$ostep->getNombre());
            $oPluginRegistry->save();
          }

          catch(Exception $e) {
            throw $e;
            echo G::json_encode(array(
            "excepcion" => $e));
          }

          echo G::json_encode(array(
            "success" => true,
            "salvando" => true,
            "respuesta" => print_r($_SESSION)
            //"parametros" => print_r($parametros),
          ));
    break;
  }
  
} catch (Exception $e) {
  echo null;
}
?>