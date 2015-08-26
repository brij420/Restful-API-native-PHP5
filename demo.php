<html>
    <head><title>JSON Request</title></head>
    <body>
        <form action="http://localhost/SID/User/" method="get"  enctype="multipart/form-data">
            <table>
                <tr>
                    <td> Action Name </td>
                    <td><textarea rows="10" cols="10" name="action" style="width:250;height:50;"></textarea></td>
                </tr>
                <tr>
                    <td> Select  </td>
                    <td><textarea rows="10" cols="10" name="select" style="width:250;height:50;"></textarea></td>
                </tr>
                <tr>
                    <td> Set  </td>
                    <td><textarea rows="10" cols="10" name="set" style="width:250;height:50;"></textarea></td>
                </tr>
                <tr>
                    <td> Condition  </td>
                    <td><textarea rows="10" cols="10" name="cond" style="width:250;height:50;"></textarea></td>
                </tr>
                <tr>
                    <td> Filter  </td>
                    <td><textarea rows="10" cols="10" name="filter" style="width:250;height:50;"></textarea></td> 
                </tr>
                <tr>
                    <td> Api Key  </td>
                    <td><textarea rows="10" cols="10" name="key" style="width:250;height:50;"></textarea></td>
                </tr>               
                <tr>
                    <td colspan="2"><input type='submit'/></td>
                </tr>
            </table>
        </form>

    </body>
</html>
<?php
# http://localhost/SID/User/?action=Get&filters={"email_username":"abforu@live.com","user_status":"1"}&select=email_username&key=1e7da8b25a5f6cd3129845ce2a1257f1&output=JSON
# http://localhost/SID/User?action=Get&set={"login_password":"abc"}&cond{"email_username":"abforu@live.com", "user_status":"1"}&conj=AND&output=JSON

/*
sidt_userschoolmap

sidt_userfollow

sidt_userdealdetails

sidt_userblock

sidt_raffle

sidt_raffletickets

sidt_posts
 * 
 * */

?>