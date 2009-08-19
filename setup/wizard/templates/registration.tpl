<h1>Register your KnowledgeTree Installation</h1>

<div class="errors">
    <?php if(isset($errors)) {
        foreach($errors as $k=>$e) {
                echo $e;
        }
    }?>
</div>

<p>
Register your KnowledgeTree Installation and receive the KnowledgeTree Drop Box for Windows software, a drag and drop tool that makes
placing documents into KnowledgeTree even easier.
</p>

<p class='disclaimer'>
We will not share your information with 3rd-parties, nor will we send you information not directly related to KnowledgeTree Inc.'s products
and services. Please see our <a href="http://www.knowledgetree.com/about/legal">Privacy and Data Retention policies</a> for more information.
</p>

<form action="index.php?step_name=registration" method="post">
<p>

<table>
    <tr>
        <td><label for='first'>First Name</label></td>
        <td rowspan='6' width='5%'>&nbsp;</td>
        <td><input name='submitted[first_name]' id='first' size='44' /></td>
        <td rowspan='6' width='5%'>&nbsp;</td>
        <td rowspan='6'>
            <img src='resources/graphics/dropbox.png' />
        </td>
    </tr>
    <tr>
        <td><label for='last'>Last Name</label></td>
        <td><input name='submitted[last_name]' id='last' size='44' /></td>
    </tr>
    <tr>
        <td><label for='email'>Email Address</label></td>
        <td><input name='submitted[email_address]' id='email' size='44' /></td>
    </tr>
    <tr>
        <td><label for='country'>Country</label></td>
        <td>
            <select id='country' name='submitted[country]'>
                <?php
                    $str = '';
                    foreach ($countries as $code => $country) {
                        $str .= "<option name='{$code}' value='{$country}'>{$country}</option>";
                    }

                    echo $str;
                ?>
            </select>
        </td>
    </tr>
    <tr>
        <td><label for='industry'>Industry</label></td>
        <td>
            <select id='industry' name='submitted[industry]'>
                <?php
                    $str = '';
                    foreach ($industries as $code => $industry) {
                        $str .= "<option name='{$code}' value='{$industry}'>{$industry}</option>";
                    }

                    echo $str;
                ?>
            </select>
        </td>
    </tr>
    <tr>
        <td><label for='size'>Organisation Size</label></td>
        <td>
            <select id='size' name='submitted[organization_size]'>
                <?php
                    $str = '';
                    foreach ($org_size as $code => $size) {
                        $str .= "<option name='{$code}' value='{$size}'>{$size}</option>";
                    }

                    echo $str;
                ?>
            </select>
        </td>
    </tr>
</table>
</p>

<?php
/*
<input type='hidden' name='submitted[telephone]' value='12345' />
<input type='hidden' name='submitted[company]' value='empty' />
<input type='hidden' name='submitted[contact]' value='12345' />
<input type='hidden' name='submitted[partner]' value='empty' />
<input type='hidden' name='submitted[hear_about_us]' value='empty' />
<input type='hidden' name='submitted[installation_guid]' value='123456' />
*/
?>

<input type='hidden' name='form_id' value='webform_client_form_242' />
<input type='hidden' name='op' value='Submit' />


<div class="buttons">
    <input type="submit" name="Previous" value="Previous"/>
    <input type="submit" name="Next" value="Register"/>
    <input type="submit" name="Confirm" value="Skip Registration"/>
</div>
</form>