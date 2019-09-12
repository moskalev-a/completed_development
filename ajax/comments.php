<?
use WS\Comments\IblockComments;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

switch ($_REQUEST["action"]){

    case "addComment":
        $returnValue = IblockComments::add($_REQUEST["userText"], $_REQUEST["projectName"], $_REQUEST["lang"], $_REQUEST["projectOriginal"]);
        echo json_encode($returnValue);
        break;

    case "replyComment":
        $returnValue = IblockComments::reply($_REQUEST["userText"], $_REQUEST["projectName"], $_REQUEST["commentId"], $_REQUEST["lang"], $_REQUEST["projectOriginal"]);
        echo json_encode($returnValue);
        break;

    case "delComment":
        $returnValue = IblockComments::dell($_REQUEST["commentId"]);
        echo json_encode($returnValue);
        break;

    case "returnComment":
        $returnValue = IblockComments::returnComment($_REQUEST["commentId"]);
        echo json_encode($returnValue);
        break;

    case "editComment":
        $returnValue = IblockComments::editComment($_REQUEST["userText"], $_REQUEST["commentId"]);
        echo json_encode($returnValue);
        break;

    case "blockComment":
        $returnValue = IblockComments::blockComment($_REQUEST["commentId"]);
        echo json_encode($returnValue);
        break;

    case "unblockComment":
        $returnValue = IblockComments::revert_user_block($_REQUEST["commentId"]);
        echo json_encode($returnValue);
        break;

    case "sortNew":
        $returnValue = IblockComments::sortComment($_REQUEST["projectName"],"desc", $_REQUEST["userId"]);
        echo json_encode($returnValue);
        break;

    case "sortOld":
        $returnValue = IblockComments::sortComment($_REQUEST["projectName"],"asc", $_REQUEST["userId"]);
        echo json_encode($returnValue);
        break;
}
?>