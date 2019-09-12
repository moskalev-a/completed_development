<?
namespace WS\Comments;
use Bitrix\Main\Loader;
use CDBResult;
use CEventMessage;
use CIBlockElement;
use CIBlockPropertyEnum;
use CIBlockSection;
use CSite;
use CUser;
use Bitrix\Highloadblock\HighloadBlockTable;

/**
 * Class IblockComments
 * @package WS\Comments
 */

class IblockComments {

    /**
     * @param $commentText
     * @param $projectID
     * @return array
     */

    public static function add($commentText, $projectID, $langId, $projectName) {

        global $USER;
        if($USER->IsAdmin()){ $isAdmin = true; }else{ $isAdmin = false; }

        if (!$commentText || !$projectID){
            $returnValue = array(
                "STATUS"  => false,
                "ISADMIN" => $isAdmin,
            );
            return $returnValue;
        }

        $commentText = trim($commentText);

        Loader::IncludeModule("iblock");

        $userID = $USER->GetID();
        $rsUser = CUser::GetByID($userID);
        $arUser = $rsUser->Fetch();

        if($arUser["NAME"] || $arUser["LAST_NAME"]){
            $userName = $arUser["NAME"] . " " . $arUser["LAST_NAME"];
        }else{
            $userName = $arUser["LOGIN"];
        }

        $el = new CIBlockElement;

        $PROP = array();
        $PROP["USER_ID"] = $userID;
        $PROP["COMMENT_DATE_TIME"] = date("d.m.Y G:i:s");
        $PROP["PROJECT_ID"] = $projectID;
        $PROP["USER_FIO"] = $userName;
        $PROP["PROJECT_NAME"] = $projectName;

        self::sender_add($userID, $projectID, $PROP["COMMENT_DATE_TIME"], $langId);

        $arLoadProductArray = Array(
            "ACTIVE"         => "Y",
            "NAME"           => $PROP["COMMENT_DATE_TIME"],
            "PROPERTY_VALUES"=> $PROP,
            "DETAIL_TEXT"    => $commentText,
            "MODIFIED_BY"    => $userID,
            "IBLOCK_SECTION_ID" => false,
            "IBLOCK_ID"      => ID_IBLOCK_COMMENTS,
        );

        if($commentID = $el->Add($arLoadProductArray)){
            $returnValue = array(
                "ID" => $commentID,
                "DATE" => $PROP["COMMENT_DATE_TIME"],
                "ISADMIN" => $isAdmin,
                "STATUS" => true,
            );
            return $returnValue;
        }else{
            $returnValue = array(
                "STATUS" => false,
                "ISADMIN" => $isAdmin,
            );
            return $returnValue;
        }
    }

    public static function reply($commentText, $projectID, $oldCommentId, $langId, $projectName) {

        global $USER;
        if($USER->IsAdmin()){ $isAdmin = true; }else{ $isAdmin = false;}

        if (!$commentText || !$projectID || !$oldCommentId){
            $returnValue = array(
                "STATUS"  => false,
                "ISADMIN" => $isAdmin,
            );
            return $returnValue;
        }

        $commentText = trim($commentText);

        Loader::IncludeModule("iblock");

        $userID = $USER->GetID();
        $rsUser = CUser::GetByID($userID);
        $arUser = $rsUser->Fetch();

        if($arUser["NAME"] || $arUser["LAST_NAME"]){
            $userName = $arUser["NAME"] . " " . $arUser["LAST_NAME"];
        }else{
            $userName = $arUser["LOGIN"];
        }

        $el = new CIBlockElement;

        $PROP = array();
        $PROP["USER_ID"] = $userID;
        $PROP["COMMENT_DATE_TIME"] = date("d.m.Y G:i:s");
        $PROP["PROJECT_ID"] = $projectID;
        $PROP["COMMENT_ANSWER_ID"] = $oldCommentId;
        $PROP["USER_FIO"] = $userName;
        $PROP["PROJECT_NAME"] = $projectName;


        self::sender_add($userID, $projectID, $PROP["COMMENT_DATE_TIME"], $langId);

        $arLoadProductArray = Array(
            "ACTIVE"         => "Y",
            "NAME"           => $PROP["COMMENT_DATE_TIME"],
            "PROPERTY_VALUES"=> $PROP,
            "DETAIL_TEXT"    => $commentText,
            "MODIFIED_BY"    => $userID,
            "IBLOCK_SECTION_ID" => false,
            "IBLOCK_ID"      => ID_IBLOCK_COMMENTS,
        );

        if($commentID = $el->Add($arLoadProductArray)){
            $returnValue = array(
                "ID" => $commentID,
                "DATE" => $PROP["COMMENT_DATE_TIME"],
                "ISADMIN" => $isAdmin,
                "STATUS" => true,
            );
            return $returnValue;
        }else{
            $returnValue = array(
                "STATUS" => false,
                "ISADMIN" => $isAdmin,
            );
            return $returnValue;
        }
    }

    public static function dell($сommentId) {

        global $USER;
        if($USER->IsAdmin()){ $isAdmin = true; }else{ $isAdmin = false;}

        if (!$сommentId){
            $returnValue = array(
                "STATUS"  => false,
                "ISADMIN" => $isAdmin,
            );
            return $returnValue;
        }

        Loader::IncludeModule("iblock");
        $userID = $USER->GetID();

        $arFilterXml = array(
            "IBLOCK_ID"=>ID_IBLOCK_COMMENTS,
            "CODE"=>"FLAG_DELETE"
        );
        $property_enums = CIBlockPropertyEnum::GetList(Array(), $arFilterXml);
        while($enum_fields = $property_enums->GetNext())
        {
            if($enum_fields["XML_ID"] == "yes"){ $flagDelId = $enum_fields["ID"];}
        }

        $el = new CIBlockElement;

        $el->SetPropertyValuesEx($сommentId, false, array("FLAG_DELETE" => $flagDelId));

        $returnValue = array(
            "ISADMIN" => $isAdmin,
            "STATUS" => true,
        );
        return $returnValue;
    }

    public static function returnComment($сommentId) {

        global $USER;
        if($USER->IsAdmin()){ $isAdmin = true; }else{ $isAdmin = false;}

        if (!$сommentId){
            $returnValue = array(
                "STATUS"  => false,
                "ISADMIN" => $isAdmin,
            );
            return $returnValue;
        }

        Loader::IncludeModule("iblock");
        $userID = $USER->GetID();
        $arFilterXml = array(
            "IBLOCK_ID"=>ID_IBLOCK_COMMENTS,
            "CODE"=>"FLAG_DELETE"
        );

        $property_enums = CIBlockPropertyEnum::GetList(Array(), $arFilterXml);
        while($enum_fields = $property_enums->GetNext())
        {
            if($enum_fields["XML_ID"] == "no"){ $flagDelId = $enum_fields["ID"];}
        }

        $el = new CIBlockElement;
        $el->SetPropertyValuesEx($сommentId, false, array("FLAG_DELETE" => $flagDelId));
        $arFilter = Array(
            "IBLOCK_ID"=>IntVal(ID_IBLOCK_COMMENTS),
            "ACTIVE"=>"Y",
            "ID" => $сommentId,
        );
        $arSelect = array("ID","NAME","DETAIL_TEXT","PROPERTY_USER_ID","PROPERTY_COMMENT_DATE_TIME","PROPERTY_COMMENT_ANSWER_ID");
        $res = CIBlockElement::GetList(Array(), $arFilter, false, array(),$arSelect);

        if($ar_fields = $res->GetNext()){

            $rsUser = CUser::GetByID($ar_fields["PROPERTY_USER_ID_VALUE"]);
            $arUser = $rsUser->Fetch();

            if($arUser["NAME"] || $arUser["LAST_NAME"]){
                $userName = $arUser["NAME"] . " " . $arUser["LAST_NAME"];
            }else{
                $userName = $arUser["LOGIN"];
            }

            if($ar_fields["PROPERTY_COMMENT_ANSWER_ID_VALUE"]){

                $arFilter["ID"] = $ar_fields["PROPERTY_COMMENT_ANSWER_ID_VALUE"];
                $res_2 = CIBlockElement::GetList(Array(), $arFilter, false, array(),$arSelect);

                if($ar_fields_2 = $res_2->GetNext()){
                    $rsUser_2 = CUser::GetByID($ar_fields_2["PROPERTY_USER_ID_VALUE"]);
                    $arUser_2 = $rsUser_2->Fetch();

                    if($arUser_2["NAME"] || $arUser_2["LAST_NAME"]){
                        $userName_2 = $arUser_2["NAME"] . " " . $arUser_2["LAST_NAME"];
                    }else{
                        $userName_2 = $arUser_2["LOGIN"];
                    }
                }

                $returnValue = array(
                    "ISADMIN" => $isAdmin,
                    "STATUS" => true,
                    "userName" => $userName,
                    "userDate" => $ar_fields["PROPERTY_COMMENT_DATE_TIME_VALUE"],
                    "userText" => $ar_fields["~DETAIL_TEXT"],
                    "replyCommentId" => $ar_fields["PROPERTY_COMMENT_ANSWER_ID_VALUE"],
                    "replyStatus" => true,
                    "replyUserName" => $userName_2,
                    "replyUserDate" => $ar_fields_2["PROPERTY_COMMENT_DATE_TIME_VALUE"],
                    "replyUserText" => $ar_fields_2["~DETAIL_TEXT"],
                );
            }else{
                $returnValue = array(
                    "ISADMIN" => $isAdmin,
                    "STATUS" => true,
                    "userName" => $userName,
                    "userDate" => $ar_fields["PROPERTY_COMMENT_DATE_TIME_VALUE"],
                    "userText" => $ar_fields["~DETAIL_TEXT"],
                    "replyStatus" => false,
                );
            }
            return $returnValue;
        }else{
            $returnValue = array(
                "ISADMIN" => $isAdmin,
                "STATUS" => false,
            );
            return $returnValue;
        }
    }

    public static function editComment($commentText, $сommentId) {

        global $USER;
        if($USER->IsAdmin()){ $isAdmin = true; } else { $isAdmin = false; }

        if (!$commentText || !$сommentId){
            $returnValue = array(
                "STATUS"  => false,
                "ISADMIN" => $isAdmin,
            );
            return $returnValue;
        }

        $commentText = trim($commentText);

        Loader::IncludeModule("iblock");

        $el = new CIBlockElement;
        $arLoadProductArray = Array(
            "DETAIL_TEXT"    => $commentText,
        );

        $res = $el->Update($сommentId, $arLoadProductArray);

        if($res){
            $returnValue = array(
                "ISADMIN" => $isAdmin,
                "STATUS" => true,
            );
        }else{
            $returnValue = array(
                "ISADMIN" => $isAdmin,
                "STATUS" => false,
            );
        }
        return $returnValue;
    }

    public static function blockComment($сommentId) {

        global $USER;
        if($USER->IsAdmin()){ $isAdmin = true; } else { $isAdmin = false; }

        if (!$сommentId){
            $returnValue = array(
                "STATUS"  => false,
                "ISADMIN" => $isAdmin,
            );
            return $returnValue;
        }

        Loader::IncludeModule("iblock");

        $arFilter = Array(
            "IBLOCK_ID"=>IntVal(ID_IBLOCK_COMMENTS),
            "ACTIVE"=>"Y",
            "ID" => $сommentId,
        );
        $arSelect = array("ID","NAME","PROPERTY_USER_ID");
        $res = CIBlockElement::GetList(Array(), $arFilter, false, array(),$arSelect);

        if($ar_fields = $res->GetNext()){

            $userId = $ar_fields["PROPERTY_USER_ID_VALUE"];
            $elUser = new CUser;
            $fieldsUser = Array(
                "UF_LOCKCOMMENT" => 1,
            );
            $elUser->Update($userId, $fieldsUser);

            $returnValue = array(
                "ISADMIN" => $isAdmin,
                "STATUS" => true,
            );
            return $returnValue;
        }else{
            $returnValue = array(
                "ISADMIN" => $isAdmin,
                "STATUS" => false,
            );
            return $returnValue;
        }
    }

    public static function revert_user_block($сommentId) {

        global $USER;
        if($USER->IsAdmin()){ $isAdmin = true; } else { $isAdmin = false; }

        if (!$сommentId){
            $returnValue = array(
                "STATUS"  => false,
                "ISADMIN" => $isAdmin,
            );
            return $returnValue;
        }

        Loader::IncludeModule("iblock");

        $arFilter = Array(
            "IBLOCK_ID"=>IntVal(ID_IBLOCK_COMMENTS),
            "ACTIVE"=>"Y",
            "ID" => $сommentId,
        );
        $arSelect = array("ID","NAME","DETAIL_TEXT","PROPERTY_USER_ID","PROPERTY_COMMENT_DATE_TIME","PROPERTY_COMMENT_ANSWER_ID");
        $res = CIBlockElement::GetList(Array(), $arFilter, false, array(),$arSelect);

        if($ar_fields = $res->GetNext()){

            $userId = $ar_fields["PROPERTY_USER_ID_VALUE"];
            $elUser = new CUser;
            $fieldsUser = Array(
                "UF_LOCKCOMMENT" => 0,
            );
            $elUser->Update($userId, $fieldsUser);

            $returnValue = array(
                "ISADMIN" => $isAdmin,
                "STATUS" => true,
            );
            return $returnValue;
        }else{
            $returnValue = array(
                "ISADMIN" => $isAdmin,
                    "STATUS" => false,
            );
        }
        return $returnValue;
    }

    public static function sortComment($projectID,$sort,$userView) {

        global $USER;
        if($USER->IsAdmin()){ $isAdmin = true; } else { $isAdmin = false; }

        if (!$projectID || !$sort){
            $returnValue = array(
                "STATUS"  => false,
                "ISADMIN" => $isAdmin,
            );
            return $returnValue;
        }

        if($userView){

            $rsUser_4 = CUser::GetByID($userView);
            if($arUser_4 = $rsUser_4->GetNext()){
                if($arUser_4["UF_LOCKCOMMENT"]){
                    $isBlock = true;
                } else {
                    $isBlock = false;
                }
            }
        }else{
            $isBlock = true;
        }

        Loader::IncludeModule("iblock");

        $arFilterXml = array(
            "IBLOCK_ID"=>ID_IBLOCK_COMMENTS,
            "CODE"=>"FLAG_DELETE"
        );
        $property_enums = CIBlockPropertyEnum::GetList(Array(), $arFilterXml);
        while($enum_fields = $property_enums->GetNext())
        {
            if($enum_fields["XML_ID"] == "yes"){ $flagDelId = $enum_fields["ID"];}
        }

        $arFilter = Array(
            "IBLOCK_ID"=>IntVal(ID_IBLOCK_COMMENTS),
            "ACTIVE"=>"Y",
            "PROPERTY_PROJECT_ID" => $projectID,
            "!PROPERTY_FLAG_DELETE" => $flagDelId,
        );
        $arSelect = array("ID","NAME","DETAIL_TEXT","PROPERTY_USER_ID","PROPERTY_COMMENT_DATE_TIME","PROPERTY_COMMENT_ANSWER_ID");
        $arOrder = array("PROPERTY_COMMENT_DATE_TIME" => $sort);
        $res = CIBlockElement::GetList($arOrder, $arFilter, false, array(),$arSelect);

        while($ar_fields = $res->GetNext()){

            $userId = $ar_fields["PROPERTY_USER_ID_VALUE"];

            $rsUser = CUser::GetByID($userId);
            $arUser = $rsUser->Fetch();

            if($arUser["NAME"] || $arUser["LAST_NAME"]){
                $userName = $arUser["NAME"] . " " . $arUser["LAST_NAME"];
            }else{
                $userName = $arUser["LOGIN"];
            }

            if($ar_fields["PROPERTY_COMMENT_ANSWER_ID_VALUE"]) $ar_fields["PROPERTY_COMMENT_ANSWER_ID_VALUE"] = (int)$ar_fields["PROPERTY_COMMENT_ANSWER_ID_VALUE"];

            if($ar_fields["PROPERTY_COMMENT_ANSWER_ID_VALUE"] && is_int($ar_fields["PROPERTY_COMMENT_ANSWER_ID_VALUE"]) && $ar_fields["PROPERTY_COMMENT_ANSWER_ID_VALUE"]>0){

                $arSelect = array("ID","NAME","DETAIL_TEXT","PROPERTY_USER_ID","PROPERTY_COMMENT_DATE_TIME","PROPERTY_COMMENT_ANSWER_ID");
                $arFilter = Array(
                    "IBLOCK_ID"=>IntVal(ID_IBLOCK_COMMENTS),
                    "ACTIVE"=>"Y",
                    "ID" => $ar_fields["PROPERTY_COMMENT_ANSWER_ID_VALUE"],
                );
                $res_2 = CIBlockElement::GetList(Array(), $arFilter, false, array(),$arSelect);

                if($ar_fields_2 = $res_2->GetNext()){
                    $rsUser_2 = CUser::GetByID($ar_fields_2["PROPERTY_USER_ID_VALUE"]);
                    $arUser_2 = $rsUser_2->Fetch();

                    if($arUser_2["NAME"] || $arUser_2["LAST_NAME"]){
                        $userName_2 = $arUser_2["NAME"] . " " . $arUser_2["LAST_NAME"];
                    }else{
                        $userName_2 = $arUser_2["LOGIN"];
                    }
                }

                $returnValue[] = array(
                    "ID" => $ar_fields["ID"],
                    "ISADMIN" => $isAdmin,
                    "isBlock" => $isBlock,
                    "STATUS" => true,
                    "userName" => $userName,
                    "userDate" => $ar_fields["PROPERTY_COMMENT_DATE_TIME_VALUE"],
                    "userText" => $ar_fields["~DETAIL_TEXT"],
                    "replyStatus" => true,
                    "replyCommentId" => $ar_fields["PROPERTY_COMMENT_ANSWER_ID_VALUE"],
                    "replyUserName" => $userName_2,
                    "replyUserDate" => $ar_fields_2["PROPERTY_COMMENT_DATE_TIME_VALUE"],
                    "replyUserText" => $ar_fields_2["~DETAIL_TEXT"],
                );
            }else{
                $returnValue[] = array(
                    "ID" => $ar_fields["ID"],
                    "ISADMIN" => $isAdmin,
                    "isBlock" => $isBlock,
                    "STATUS" => true,
                    "userName" => $userName,
                    "userDate" => $ar_fields["PROPERTY_COMMENT_DATE_TIME_VALUE"],
                    "userText" => $ar_fields["~DETAIL_TEXT"],
                    "replyStatus" => false,
                );
            }
        }
        return $returnValue;
    }

    public static function sender_add($userID, $projectID, $dataTime, $langId) {
        if (!$userID || !$projectID || !$dataTime){
            $returnValue = array(
                "STATUS"  => false,
            );
            return $returnValue;
        }

        global $USER;
        Loader::IncludeModule('highloadblock');

        $hlblock_id = ID_HIBLOCK_SENDER_USER;
        $hlblock   = HighloadBlockTable::getById( $hlblock_id )->fetch();
        $entity   = HighloadBlockTable::compileEntity( $hlblock );
        $entity_data_class = $entity->getDataClass();
        $entity_table_name = $hlblock['TABLE_NAME'];
        $sTableID = 'tbl_'.$entity_table_name;

        $arFilterHi = array("UF_C_USER_ID" => $userID);
        $arSelectHi = array('*');
        $rsDataHi = $entity_data_class::getList(array(
            "select" => $arSelectHi,
            "filter" => $arFilterHi,
        ));
        $rsDataHi = new CDBResult($rsDataHi, $sTableID);

        if(!$arRes = $rsDataHi->GetNext()){

            $cKey = md5($USER->GetEmail()."|".$userID."|".$projectID);

            $arDataHi = Array(
                'UF_C_USER_ID' => $userID,
                'UF_C_PROJECT_IDS' => array($projectID),
                'UF_C_EMAIL' => $USER->GetEmail(),
                'UF_C_KEY' => $cKey,
            );
            $entity_data_class::add($arDataHi);
        }else{

            if($arRes["UF_C_PROJECT_IDS"]){

                if(!in_array($projectID,$arRes["UF_C_PROJECT_IDS"])){

                    array_push($arRes["UF_C_PROJECT_IDS"],$projectID);
                    $arNewData = array(
                        'UF_C_PROJECT_IDS' => $arRes["UF_C_PROJECT_IDS"]
                    );
                    $entity_data_class::update($arRes["ID"], $arNewData);
                }
            }else{

                $arNewData = array(
                    'UF_C_PROJECT_IDS' => array($projectID)
                );
                $entity_data_class::update($arRes["ID"], $arNewData);
            }
        }

        $hlblock_id = ID_HIBLOCK_SENDER_EVENT;
        $hlblock   = HighloadBlockTable::getById( $hlblock_id )->fetch();
        $entity   = HighloadBlockTable::compileEntity( $hlblock );
        $entity_data_class = $entity->getDataClass();
        $entity_table_name = $hlblock['TABLE_NAME'];
        $sTableID = 'tbl_'.$entity_table_name;

        $arDataHi = Array(
            'UF_C_USER_ID' => $userID,
            'UF_C_PROJECT_ID' => $projectID,
            "UF_C_DATA" => $dataTime,
            "UF_C_LANGUAGE_ID" => $langId,
        );
        $entity_data_class::add($arDataHi);

        $returnValue = array(
            "STATUS" => true,
        );
        return $returnValue;
    }

    public static function sender_delete() {

        $messageNameNot = "Не удается выполнить отписку от уведомлений. Обратитесь к администратору сайта";
        $messageNameYes = "Вы больше не будете получать уведомления о новых комментариях в проектах.";

        if(LANGUAGE_ID == "en"){
            $messageNameNot = "Unable to unsubscribe from notifications. Contact your site administrator";
            $messageNameYes = "You will no longer be notified of new comments in projects.";
        }

        if(!$_REQUEST["key"]){

            $returnValue = array(
                "STATUS" => false,
                "MESSAGE" => $messageNameNot,
            );
            return $returnValue;
        }else{

            $key = $_REQUEST["key"];
        }

        global $USER;
        Loader::IncludeModule('highloadblock');

        $hlblock_id = ID_HIBLOCK_SENDER_USER;
        $hlblock   = HighloadBlockTable::getById( $hlblock_id )->fetch();
        $entity   = HighloadBlockTable::compileEntity( $hlblock );
        $entity_data_class = $entity->getDataClass();
        $entity_table_name = $hlblock['TABLE_NAME'];
        $sTableID = 'tbl_'.$entity_table_name;

        $arFilterHi = array("UF_C_KEY" => $key);
        $arSelectHi = array('*');
        $rsDataHi = $entity_data_class::getList(array(
            "select" => $arSelectHi,
            "filter" => $arFilterHi,
        ));
        $rsDataHi = new CDBResult($rsDataHi, $sTableID);

        if($arRes = $rsDataHi->GetNext()){

            $result = $entity_data_class::delete($arRes["ID"]);

            if ($result->isSuccess()) {
                $returnValue = array(
                    "STATUS" => true,
                    "MESSAGE" => $messageNameYes,
                );
            } else {
                $returnValue = array(
                    "STATUS" => false,
                    "MESSAGE" => $messageNameNot,
                );
            }
        }else{
            $returnValue = array(
                "STATUS" => false,
                "MESSAGE" => $messageNameNot,
            );
        }
        return $returnValue;
    }

    public static function sender_agent() {

        global $USER;
        Loader::IncludeModule('highloadblock');
        Loader::IncludeModule("iblock");

        $hlblock_id = ID_HIBLOCK_SENDER_EVENT;
        $hlblock   = HighloadBlockTable::getById( $hlblock_id )->fetch();
        $entity   = HighloadBlockTable::compileEntity( $hlblock );
        $entity_data_class = $entity->getDataClass();
        $entity_table_name = $hlblock['TABLE_NAME'];
        $sTableID = 'tbl_'.$entity_table_name;

        $arSelectHi = array('*');
        $rsDataHi = $entity_data_class::getList(array(
            "select" => $arSelectHi,
        ));
        $rsDataHi = new CDBResult($rsDataHi, $sTableID);

        while($arRes = $rsDataHi->GetNext()){

            $arResCom[] = array(
                "ID" => $arRes["ID"],
                "UF_C_DATA" => $arRes["UF_C_DATA"],
                "UF_C_USER_ID" => $arRes["UF_C_USER_ID"], //исключающий ключ
                "UF_C_PROJECT_ID" => $arRes["UF_C_PROJECT_ID"], //связующий ключ
                "UF_C_LANGUAGE_ID" => $arRes["UF_C_LANGUAGE_ID"],
            );

            $arResDel[] = $arRes["ID"];
        }

        if($arResCom){

            foreach ($arResCom as $arValCom){

                if($arValCom["UF_C_PROJECT_ID"]){



                    $hlblock_id = ID_HIBLOCK_SENDER_USER;
                    $hlblock   = HighloadBlockTable::getById( $hlblock_id )->fetch();
                    $entity   = HighloadBlockTable::compileEntity( $hlblock );
                    $entity_data_class_2 = $entity->getDataClass();
                    $entity_table_name = $hlblock['TABLE_NAME'];
                    $sTableID = 'tbl_'.$entity_table_name;

                    $arFilterHi = array("UF_C_PROJECT_IDS" => $arValCom["UF_C_PROJECT_ID"]);
                    $arSelectHi = array('*');
                    $rsDataHi = $entity_data_class_2::getList(array(
                        "select" => $arSelectHi,
                        "filter" => $arFilterHi,
                    ));
                    $rsDataHi = new CDBResult($rsDataHi, $sTableID);

                    while($arRes = $rsDataHi->GetNext()) {

                        if($arValCom["UF_C_USER_ID"]!=$arRes["UF_C_USER_ID"]){

                            $arResSend[] = array(
                                "UF_C_EMAIL" => $arRes["UF_C_EMAIL"],
                                "UF_C_PROJECT_ID" => $arValCom["UF_C_PROJECT_ID"],
                                "UF_C_KEY" => $arRes["UF_C_KEY"],
                                "ID_SENDER" => $arValCom["ID"],
                                "UF_C_LANGUAGE_ID" => $arValCom["UF_C_LANGUAGE_ID"],
                            );
                        }
                    }
                }
            }

            if($arResSend){

                foreach ($arResSend as $keyValCom2 => $arValCom_2){

                    if($arValCom_2["UF_C_PROJECT_ID"]){

                        if($arValCom_2["UF_C_LANGUAGE_ID"]=="ru"){ $idProjectIs = ID_IBLOCK_PROJECTS; } else{ $idProjectIs = ID_IBLOCK_PROJECTS_EN; }

                        $arSelect = array("ID","NAME","SECTION_PAGE_URL");
                        $arFilter = array(
                            "IBLOCK_ID"=>IntVal($idProjectIs),
                            "ID" => $arValCom_2["UF_C_PROJECT_ID"],
                        );

                        $rsSections = CIBlockSection::GetList(array(), $arFilter, false,$arSelect);

                        if($arFields = $rsSections->GetNext())
                        {
                            $arResSend[$keyValCom2]["NAME"] = $arFields["NAME"];
                            $arResSend[$keyValCom2]["SECTION_PAGE_URL"] = $arFields["SECTION_PAGE_URL"];
                        }
                    }
                }
                self::sender_send($arResSend);
            }
        }

        if($arResDel){

            foreach ($arResDel as $arDelVal){

                $entity_data_class::delete($arDelVal);
            }
        }


        return "\WS\Comments\IblockComments::sender_agent();";
    }

    public static function sender_send($arResSend) {

        if($arResSend){

            $rsSites = CSite::GetByID('s1');
            $arSite = $rsSites->Fetch();
            $Link = 'http://'.$arSite['SERVER_NAME'];
            $siteLink = $Link;
            $siteName = $arSite['SERVER_NAME'];
            $LinkEn = $Link . "/en";
            $siteLinkEn = $LinkEn . "/";

            $adminEmails = [];
            $filter = ['GROUPS_ID'=>1];
            $rsUsers = CUser::GetList(($by="id"), ($order="desc"), $filter);
            while ($res = $rsUsers->Fetch()){
                $adminEmails[] = $res['EMAIL'];
            }

            foreach ($arResSend as $arValCom_2){

                if($arValCom_2["UF_C_LANGUAGE_ID"] == 'en'){
                    $LinkSend = $LinkEn;
                    $siteLinkSend = $siteLinkEn;
                }else{
                    $LinkSend = $Link;
                    $siteLinkSend = $siteLink;
                }
                if(!in_array($arValCom_2["UF_C_EMAIL"], $adminEmails)){
                    $eventFields = array(
                        'EMAIL' => $arValCom_2["UF_C_EMAIL"],
                        'PROJECT_NAME' => $arValCom_2["NAME"],
                        'LINK' => $LinkSend . $arValCom_2["SECTION_PAGE_URL"],
                        'REVERT_SENDER' => $LinkSend . "/sender/?key=" . $arValCom_2["UF_C_KEY"],
                        'SITE_NAME' => $siteName,
                        'SITE_LINK' => $siteLinkSend,
                    );

                    $arFilter = [ "TYPE_ID" => "COMMENT_EVENTS_NEW_SEND" ];
                    $rsMess = CEventMessage::GetList($by="site_id", $order="desc", $arFilter);
                    while($arMess = $rsMess->GetNext())
                    {
                        $messageId[($arMess['LANGUAGE_ID'] == null) ? 'en' : 'ru'] = $arMess['ID'];
                    }

                    if($arValCom_2["UF_C_LANGUAGE_ID"] == 'ru'){
                        \CEvent::Send(COMMENT_EVENTS_NEW_SEND, 's1', $eventFields,'Y',$messageId['ru'], array(), $arValCom_2["UF_C_LANGUAGE_ID"]);
                    } elseif($arValCom_2["UF_C_LANGUAGE_ID"] == 'en'){
                        \CEvent::Send(COMMENT_EVENTS_NEW_SEND, 's1', $eventFields,'Y',$messageId['en'], array(), $arValCom_2["UF_C_LANGUAGE_ID"]);
                    }
                }
            }
        }

    }
}