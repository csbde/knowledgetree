<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>KTJapi Test</title>
<link rel="stylesheet" type="text/css" href="ktjapi.css" />
<script src="lib/jquery.js"></script>
<script src="lib/kt.js"></script>
<script src="lib/kt.evt.js"></script>
<script src="lib/kt.vars.js"></script>
<script src="lib/kt.lib.js"></script>
<script src="lib/kt.debug.js"></script>
<script src="lib/ktjapi.all.js"></script>
<script src="app.js"></script>
</head>
<body>
<table width="1020" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td width="420" valign="top"><div class="main">
      <div class="header top">KnowledgeTree KTJAPI Test Console</div>
      <div class="content">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td class="inputHeading">Authentication Parameters</td>
          </tr>
          <tr>
            <td><table width="100%" border="0" cellpadding="0" cellspacing="0" class="inputSection">
                <tr>
                  <td width="30%" class="inputTitle">Server</td>
                  <td width="70%" class="inputCell"><input name="server" type="text" class="textInput" id="server" value="https://ktdms.knowledgetreelive.com/webservice/clienttools/comms.php" /></td>
                </tr>
                <!-- tr>
                  <td class="inputTitle">User</td>
                  <td class="inputCell"><input name="user" type="text" class="textInput" id="user" value="admin" /></td>
                </tr>
                <tr>
                  <td class="inputTitle">Pass</td>
                  <td class="inputCell"><input name="pass" type="text" class="textInput" id="pass" value="admin" /></td>
                </tr>
                <tr>
                  <td class="inputTitle">App Type</td>
                  <td class="inputCell"><input name="apptype" type="text" class="textInput" id="apptype" value="air" /></td>
                </tr>
                <tr>
                  <td class="inputTitle">Version</td>
                  <td class="inputCell"><input name="version" type="text" class="textInput" id="version" value="0.2" /></td>
                </tr -->
                <tr>
                  <td class="inputTitle">Session_ID</td>
                  <td class="inputCell"><input name="session_id" type="text" class="textInput disabled" id="session_id" disabled="disabled" /></td>
                </tr>
                <tr>
                  <td class="inputTitle">Token</td>
                  <td class="inputCell"><input name="token" type="text" class="textInput disabled" id="token" disabled="disabled" /></td>
                </tr>
                <tr>
                  <td class="inputTitle">Include Debug</td>
                  <td class="inputCell"><input name="checkbox" type="checkbox" id="debug" value="1" checked="checked" /></td>
                </tr>
                <tr>
                  <td class="inputTitle">As Datasource [POST]</td>
                  <td class="inputCell"><input name="checkbox" type="checkbox" id="datasource" value="1" checked="checked" /></td>
                </tr>
            </table></td>
          </tr>
          <tr>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td class="inputHeading">Request Parameters</td>
          </tr>
          <tr>
            <td><table width="100%" border="0" cellpadding="0" cellspacing="0" class="inputSection">
                <tr>
                  <td width="30%" class="inputTitle">Service</td>
                  <td width="70%" class="inputCell"><input name="r_service" type="text" class="textInput" id="r_service" value="system" /></td>
                </tr>
                <tr>
                  <td class="inputTitle">Function</td>
                  <td class="inputCell"><input name="r_function" type="text" class="textInput" id="r_function" value="checkVersion" /></td>
                </tr>
                <tr>
                  <td class="inputDivision"><div style="display:none;"></div></td>
                  <td class="inputDivision"><div style="display:none;"></div></td>
                </tr>
                <tr>
                  <td class="altInputCell"><input name="n1" type="text" class="textInput" id="n1" value="#paramName" style="text-align:right; font-weight:bold; padding-right: 1px;" onfocus="this.select();" />                  </td>
                  <td class="inputCell"><input name="v1" type="text" class="textInput" id="v1" /></td>
                </tr>
                <tr>
                  <td class="altInputCell"><input name="n2" type="text" class="textInput" id="n2" value="#paramName" style="text-align:right; font-weight:bold; padding-right: 1px;" onfocus="this.select();" />                  </td>
                  <td class="inputCell"><input name="v2" type="text" class="textInput" id="v2" /></td>
                </tr>
                <tr>
                  <td class="altInputCell"><input name="n3" type="text" class="textInput" id="n3" value="#paramName" style="text-align:right; font-weight:bold; padding-right: 1px;" onfocus="this.select();" />                  </td>
                  <td class="inputCell"><input name="v3" type="text" class="textInput" id="v3" /></td>
                </tr>
                <tr>
                  <td class="altInputCell"><input name="n4" type="text" class="textInput" id="n4" value="#paramName" style="text-align:right; font-weight:bold; padding-right: 1px;" onfocus="this.select();" />                  </td>
                  <td class="inputCell"><input name="v4" type="text" class="textInput" id="v4" /></td>
                </tr>
                <tr>
                  <td class="altInputCell"><input name="n5" type="text" class="textInput" id="n5" value="#paramName" style="text-align:right; font-weight:bold; padding-right: 1px;" onfocus="this.select();" />                  </td>
                  <td class="inputCell"><input name="v5" type="text" class="textInput" id="v5" /></td>
                </tr>
                <tr>
                  <td class="altInputCell"><input name="n6" type="text" class="textInput" id="n6" value="#paramName" style="text-align:right; font-weight:bold; padding-right: 1px;" onfocus="this.select();" />                  </td>
                  <td class="inputCell"><input name="v6" type="text" class="textInput" id="v6" /></td>
                </tr>
                <tr>
                  <td class="altInputCell"><input name="n7" type="text" class="textInput" id="n7" value="#paramName" style="text-align:right; font-weight:bold; padding-right: 1px;" onfocus="this.select();" />                  </td>
                  <td class="inputCell"><input name="v7" type="text" class="textInput" id="v7" /></td>
                </tr>
                <tr>
                  <td class="altInputCell"><input name="n8" type="text" class="textInput" id="n8" value="#paramName" style="text-align:right; font-weight:bold; padding-right: 1px;" onfocus="this.select();" />                  </td>
                  <td class="inputCell"><input name="v8" type="text" class="textInput" id="v8" /></td>
                </tr>
                <tr>
                  <td class="altInputCell"><input name="n9" type="text" class="textInput" id="n9" value="#paramName" style="text-align:right; font-weight:bold; padding-right: 1px;" onfocus="this.select();" />                  </td>
                  <td class="inputCell"><input name="v9" type="text" class="textInput" id="v9" /></td>
                </tr>
                <tr>
                  <td class="altInputCell"><input name="n10" type="text" class="textInput" id="n10" value="#paramName" style="text-align:right; font-weight:bold; padding-right: 1px;" onfocus="this.select();" />                  </td>
                  <td class="inputCell"><input name="v10" type="text" class="textInput" id="v10" /></td>
                </tr>
            </table></td>
          </tr>
          <tr>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td class="inputSubmitCell"><input type="button" name="button" id="button" value="Execute" onclick="kt.evt.trigger('execute');" /></td>
          </tr>
        </table>
      </div>
    </div></td>
    <td width="600" valign="top"><div class="output">
      <div class="header top">Output</div>
      <div id="uri"></div>
      <div id="datasourceuri"></div>
      <div id="error"> Err Msg</div>
      <!--  div id="r_error_b"></div>
      <div class="divider"></div -->
      <div style="padding:5px;">
          <div class="header">Response Data</div>
          <div id="r_data"></div>
          <div class="divider"></div>
          
          <div class="header">Errors Object</div>
          <div id="r_errors"></div>
          <div class="divider"></div>
                    
          <div class="header">Request Object</div>
          <div id="r_request"></div>
          <div class="divider"></div>
          
          <div class="header">Status Object</div>
          <div id="r_status"></div>
          <div class="divider"></div>
          
          <div class="header">Debug Object</div>
          <div id="r_debug"></div>
          <div class="divider"></div>
          
          <div class="header">LOG</div>
          <div id="r_log"></div>
          <div class="divider"></div>
    </div>
    </div></td>
  </tr>
</table>
</body>
</html>
