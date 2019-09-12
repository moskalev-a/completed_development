<?
use WS\Helpers\IblockHelper;

AddEventHandler("iblock", "OnBeforeIBlockElementAdd", ["IBlockEx", "OnBeforeIBlockElementUpdateHandler"]);
AddEventHandler("iblock", "OnBeforeIBlockElementUpdate", ["IBlockEx", "OnBeforeIBlockElementUpdateHandler"]);

AddEventHandler("iblock", "OnBeforeIBlockElementAdd", ["IBlockEx", "OnBeforeIBlockElementUpdateVideo"]);
AddEventHandler("iblock", "OnBeforeIBlockElementUpdate", ["IBlockEx", "OnBeforeIBlockElementUpdateVideo"]);

class IBlockEx{
    function OnBeforeIBlockElementUpdateVideo($arFields){

        $iblockSection = $arFields['IBLOCK_SECTION'][0];
        $idElement = $arFields["ID"];

        switch ($arFields['IBLOCK_ID']){

            case ID_IBLOCK_PROJECTS:

                IBlockEx::editProjectElem($arFields,$iblockSection,$idElement, ID_IBLOCK_PROJECTS, ID_IBLOCK_VIDEO);
                break;

            case ID_IBLOCK_PROJECTS_EN:

                IBlockEx::editProjectElem($arFields,$iblockSection,$idElement, ID_IBLOCK_PROJECTS_EN, ID_IBLOCK_VIDEO_EN);
                break;

            case ID_IBLOCK_VIDEO:

                IBlockEx::editVideoElem($arFields,$iblockSection,$idElement, ID_IBLOCK_PROJECTS, ID_IBLOCK_VIDEO);
                break;

            case ID_IBLOCK_VIDEO_EN:

                IBlockEx::editVideoElem($arFields,$iblockSection,$idElement, ID_IBLOCK_PROJECTS_EN, ID_IBLOCK_VIDEO_EN);
                break;
        }
    }

    function editProjectElem($arFields,$iblockSection,$idElement, $setIblockProject, $setIblockVideo){

        $arSort = array("sort" => "asc");
        $arFilter = array("CODE" => "ELEMENT_TYPE");
        $arRes = CIBlockElement::GetProperty($setIblockProject, $arFields["ID"], $arSort, $arFilter);
        if($arOb = $arRes->Fetch()) $arValue = $arOb["VALUE_ENUM"];

        if($arValue == "Видео" && $iblockSection){

            $tempProp = IblockHelper::getPropertyByCode($setIblockProject, "VIDEO");
            $IdVidProj = $tempProp["ID"];
            $arVidProjectTemp = $arFields["PROPERTY_VALUES"][$IdVidProj];

            foreach ($arVidProjectTemp as $arVidVal){
                if($arVidVal["VALUE"]) $arVidProject[] = $arVidVal["VALUE"];
            }

            $el = new CIBlockElement;
            $arFilter = Array(
                "IBLOCK_ID"                     => $setIblockProject,
                "ACTIVE"                        => "Y",
                "ID"                            => $idElement,
            );
            $arSelect = array("ID","IBLOCK_ID","NAME","PROPERTY_VIDEO*");
            $arRes = CIBlockElement::GetList(array(), $arFilter, false, array(),$arSelect);

            if($arOb = $arRes->GetNextElement()) {

                $arListProps = $arOb->GetProperties();
                $arVidProjectOld = $arListProps['VIDEO']["VALUE"];
            }

            if($arVidProject && $arVidProjectOld){

                $arVidNew = array_diff($arVidProject, $arVidProjectOld);

                if($arVidNew){

                    foreach ($arVidNew as $arVal){

                        $arFilter = Array(
                            "IBLOCK_ID"         => $setIblockProject,
                            "ACTIVE"            => "Y",
                            "PROPERTY_VIDEO"    => $arVal,
                            "!ID"               => $idElement,
                        );
                        $arSelect = array("ID","IBLOCK_ID","NAME","PROPERTY_VIDEO");
                        $arRes = CIBlockElement::GetList(array(), $arFilter, false, array(),$arSelect);

                        while($arOb = $arRes->GetNextElement()) {

                            $arListFields = $arOb->GetFields();
                            $arListProps = $arOb->GetProperties();
                            $arVidProjectOld = $arListProps['VIDEO']["VALUE"];
                            $arVal = array($arVal);
                            $arVidProjectNew = array_diff($arVidProjectOld, $arVal);
                            $el->SetPropertyValuesEx($arListFields["ID"], $setIblockProject, array("VIDEO" => $arVidProjectNew));
                        }
                    }
                }

                $arFilter = Array(
                    "IBLOCK_ID"     => $setIblockVideo,
                    "ACTIVE"        => "Y",
                    "ID"            => $arVidProject,
                );
                $arSelect = array("ID","NAME","PROPERTY_PROJECT_ID","PROPERTY_PROJECT_LINK_TO");
                $arRes = CIBlockElement::GetList(Array(), $arFilter, false, array(),$arSelect);
                while($arOb = $arRes->GetNext()){

                    if($arOb['PROPERTY_PROJECT_LINK_TO_VALUE'] != $idElement){

                        $el->SetPropertyValuesEx($arOb["ID"], $setIblockVideo, array("PROJECT_LINK_TO" => $idElement));
                    }

                    if($arOb['PROPERTY_PROJECT_ID_TO_VALUE'] != $iblockSection){

                        $el->SetPropertyValuesEx($arOb["ID"], $setIblockVideo, array("PROJECT_ID" => $iblockSection));
                    }
                }

                $arVidDel = array_diff($arVidProjectOld, $arVidProject);

                if($arVidDel){

                    foreach ($arVidDel as $arVal){

                        $el->SetPropertyValuesEx($arVal, $setIblockVideo, array("PROJECT_LINK_TO" => ""));
                        $el->SetPropertyValuesEx($arVal, $setIblockVideo, array("PROJECT_ID" => ""));
                    }
                }
            }elseif($arVidProject && !$arVidProjectOld){

                $arFilter = Array(
                    "IBLOCK_ID"     => $setIblockVideo,
                    "ACTIVE"        => "Y",
                    "ID"            => $arVidProject,
                );
                $arSelect = array("ID","NAME","PROPERTY_PROJECT_ID","PROPERTY_PROJECT_LINK_TO");
                $arRes = CIBlockElement::GetList(Array(), $arFilter, false, array(),$arSelect);
                while($arOb = $arRes->GetNext()){

                    if($arOb['PROPERTY_PROJECT_LINK_TO_VALUE'] != $idElement){

                        $el->SetPropertyValuesEx($arOb["ID"], $setIblockVideo, array("PROJECT_LINK_TO" => $idElement));
                    }

                    if($arOb['PROPERTY_PROJECT_ID_TO_VALUE'] != $iblockSection){

                        $el->SetPropertyValuesEx($arOb["ID"], $setIblockVideo, array("PROJECT_ID" => $iblockSection));
                    }
                }

                foreach ($arVidProject as $arVal) {

                    $arFilter = Array(
                        "IBLOCK_ID"         => $setIblockProject,
                        "ACTIVE"            => "Y",
                        "PROPERTY_VIDEO"    => $arVal,
                        "!ID"               => $idElement,
                    );
                    $arSelect = array("ID","IBLOCK_ID","NAME","PROPERTY_VIDEO");
                    $arRes = CIBlockElement::GetList(array(), $arFilter, false, array(),$arSelect);

                    while($arOb = $arRes->GetNextElement()) {

                        $arListFields = $arOb->GetFields();
                        $arListProps = $arOb->GetProperties();
                        $arVidProjectOld = $arListProps['VIDEO']["VALUE"];
                        $arVal = array($arVal);
                        $arVidProjectNew = array_diff($arVidProjectOld, $arVal);
                        $el->SetPropertyValuesEx($arListFields["ID"], $setIblockProject, array("VIDEO" => $arVidProjectNew));
                    }
                }
            }elseif(!$arVidProject && $arVidProjectOld){

                foreach ($arVidProjectOld as $arVal){

                    $el->SetPropertyValuesEx($arVal, $setIblockVideo, array("PROJECT_LINK_TO" => ""));
                    $el->SetPropertyValuesEx($arVal, $setIblockVideo, array("PROJECT_ID" => ""));
                }
            }
        }
    }

    function editVideoElem($arFields,$iblockSection,$idElement, $setIblockProject, $setIblockVideo){

        $tempProp = IblockHelper::getPropertyByCode($setIblockVideo, "PROJECT_LINK_TO");
        $IdVidVid = $tempProp["ID"];
        $arVidVidTemp = $arFields["PROPERTY_VALUES"][$IdVidVid];

        foreach ($arVidVidTemp as $arVidVal){
            if($arVidVal["VALUE"]) $arVidVideo = $arVidVal["VALUE"];
        }

        $arSort = array("sort" => "asc");
        $arFilter = array("CODE" => "PROJECT_LINK_TO");
        $arRes = CIBlockElement::GetProperty($setIblockVideo, $arFields["ID"], $arSort, $arFilter);
        if($arOb = $arRes->Fetch()) $arValueOld = $arOb["VALUE"];

        if($arVidVideo){

            $el = new CIBlockElement;
            $arFilter = Array(
                "IBLOCK_ID" => $setIblockProject,
                "ACTIVE"    => "Y",
                "ID"        => $arVidVideo,
            );
            $arSelect = array("ID","IBLOCK_ID","NAME","PROPERTY_*");
            $arRes = CIBlockElement::GetList(array(), $arFilter, false, array(),$arSelect);

            if($arOb = $arRes->GetNextElement()){

                $arListFields = $arOb->GetFields();
                $arListProps = $arOb->GetProperties();

                $arVidProject = $arListProps['VIDEO']["VALUE"];
                $idElement = (string)$idElement;

                if(!in_array($idElement, $arVidProject)){

                    $arVidProject[] = $idElement;
                    $el->SetPropertyValuesEx($arListFields["ID"], $setIblockProject, array("VIDEO" => $arVidProject));
                }
            }

            $arVidProject = array();
            if($arValueOld && $arVidVideo != $arValueOld){

                $arFilter = Array(
                    "IBLOCK_ID" => $setIblockProject,
                    "ACTIVE"    => "Y",
                    "ID"        => $arValueOld,
                );
                $arSelect = array("ID","IBLOCK_ID","NAME","PROPERTY_*");
                $arRes = CIBlockElement::GetList(array(), $arFilter, false, array(),$arSelect);

                if($arOb = $arRes->GetNextElement()){

                    $arListFields = $arOb->GetFields();
                    $arListProps = $arOb->GetProperties();

                    $arVidProject = $arListProps['VIDEO']["VALUE"];
                    $idElement = (string)$idElement;

                    if(in_array($idElement, $arVidProject)){

                        $idElement = array($idElement);
                        $arVidDel = array_diff($arVidProject, $idElement);
                        if($arVidProject == $idElement) $arVidDel = "";
                        $el->SetPropertyValuesEx($arListFields["ID"], $setIblockProject, array("VIDEO" => $arVidDel));
                    }
                }
            }
        }elseif($arValueOld){

            $el = new CIBlockElement;
            $arFilter = Array(
                "IBLOCK_ID" => $setIblockProject,
                "ACTIVE"    => "Y",
                "ID"        => $arValueOld,
            );
            $arSelect = array("ID","IBLOCK_ID","NAME","PROPERTY_*");
            $arRes = CIBlockElement::GetList(array(), $arFilter, false, array(),$arSelect);

            while($arOb = $arRes->GetNextElement()){

                $arListFields = $arOb->GetFields();
                $arListProps = $arOb->GetProperties();

                $arVidProject = $arListProps['VIDEO']["VALUE"];
                $idElement = (string)$idElement;

                if(in_array($idElement, $arVidProject)){

                    $idElement = array($idElement);
                    $arVidDel = array_diff($arVidProject, $idElement);
                    if($arVidProject == $idElement) $arVidDel = "";
                    $el->SetPropertyValuesEx($arListFields["ID"], $setIblockProject, array("VIDEO" => $arVidDel));
                }

            }
        }
    }
}