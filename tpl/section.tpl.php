<!--[section.title; strconv=no;]-->

<table class="border" width="100%">
	<tr>
		<td width="20%">[section.ref_title; strconv=no;]</td>
		<td>[section.ref; strconv=no;]</td>
	</tr>
	<tr>
		<td width="20%">[section.title_title; strconv=no;]</td>
		<td>[section.title; strconv=no;]</td>
	</tr>
	<tr>
		<td width="20%">[section.fk_usergroup_title; strconv=no;]</td>
		<td>[section.fk_usergroup; strconv=no;]</td>
	</tr>
    <?php
    if(isset($_GET[plan_section]))
    {
    echo"     
        <tr>
            <td width="20%">[section.fk_usergroup_title; strconv=no;]</td>
            <td>[section.fk_usergroup; strconv=no;]</td>
        </tr>";
    }
    ?>
</table>

<br />

<center>[buttons.buttons; strconv=no;]</center>