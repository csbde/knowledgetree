<?php

include_once(KT_ATOM_LIB_FOLDER.'KT_atom_responseFeed.inc.php');

class KT_cmis_atom_responseFeed extends KT_atom_responseFeed {

    // override and extend as needed

}

class KT_cmis_atom_ResponseFeed_GET extends KT_cmis_atom_responseFeed{}
class KT_cmis_atom_ResponseFeed_PUT extends KT_cmis_atom_responseFeed{}
class KT_cmis_atom_ResponseFeed_POST extends KT_cmis_atom_responseFeed{}
class KT_cmis_atom_ResponseFeed_DELETE extends KT_cmis_atom_responseFeed{}

?>