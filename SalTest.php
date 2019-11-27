<?php
session_start();?>
<html lang="en">
<head>
    <title>Hr Data</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/ui/1.10.4/jquery-ui.js" xmlns:background-color="http://www.w3.org/1999/xhtml"
            xmlns:background-color="http://www.w3.org/1999/xhtml" xmlns:background-color="http://www.w3.org/1999/xhtml"
            xmlns:background-color="http://www.w3.org/1999/xhtml" xmlns:background-color="http://www.w3.org/1999/xhtml"></script>


</head>
<nav class="navbar navbar-inverse">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="#"></a>
        </div>
        <ul class="nav navbar-nav">
            <li class="active"><a href=""><?php echo "branch code=".$_SESSION['brcode']; ?></a></li>
            <li class="active"><a href=""><?php echo $_SESSION['name'] ?></a></li>
        </ul>
        <button class="btn btn-danger navbar-btn"><a href="salary.php" style="color:black"   >   Basic & PF </a></button>
        <button class="btn btn-danger navbar-btn"><a href="view.php" style="color:black"   >   View </a></button>
        <button class="btn btn-danger navbar-btn"><a href="?logout=true" style="color:red"   >   Logout </a></button>
    </div>
</nav>



<?php
//session_start();
//$students[] = 50;
if($_SESSION['brcode'] == NULL){
    header('Location:login.php');
}
/*
echo "Branch Code=".$_SESSION['brcode'];
echo " | ".$_SESSION['name'] ;*/

$br=$_SESSION['brcode'];

$c = oci_connect('ORBHRM', 'ORBHRM', '(DESCRIPTION =(ADDRESS = (PROTOCOL = TCP)(HOST =40.20.21.2)(PORT = 1521))(CONNECT_DATA =(SERVER = DEDICATED)(SERVICE_NAME = psbdb1)))');

if (!$c) {
    $m = oci_error();
    trigger_error('Could not connect to database: ' . $m['message'], E_USER_ERROR);
}

/*$s = oci_parse($c, "    select pydepcde,PYEMPCDE,PYEMPNAM, pyacctno, pybaspay  from pyempmas
    where pycomcde = '200'
    and PYACCTNO is not null order by pydepcde asc");*/

$s = oci_parse($c, "select A.PYDEPCDE BR_CODE, b.costdesc BRN_NAME, a.pyempcde EMP_ID, to_char(a.pyempnam) EMP_NAME, a.PYACCTNO ACCOUNT_NO, a.PYPIMSNM PIMS,  DPR_CODE_DESC('999', a.pydescde) designatiom,
     (select PYAMOUNT
     from pyfxtrns
      where pycomcde = a.pycomcde
and pyempcde = a.pyempcde
and PYERDDCD in('ER000','ER011'))SAL
from pyempmas a, syjobmas b
where a.pycomcde = '200' and a.PYDEPCDE='$br'
and b.compcode = a.pycomcde
and a.pydepcde = b.costcode order by a.pydepcde asc ");

if (!$s) {
    $m = oci_error($c);
    trigger_error('Could not parse statement: ' . $m['message'], E_USER_ERROR);
}
$r = oci_execute($s);
if (!$r) {
    $m = oci_error($s);
    trigger_error('Could not execute statement: ' . $m['message'], E_USER_ERROR);
}
?>
<p>Date: <span id="datetime"></span></p>
<script>
    var dt = new Date();
    document.getElementById("datetime").innerHTML = dt.toLocaleDateString();
</script>


<div class="container">
    <form action="" method="post"   enctype="multipart/form-data">
    <table border="1" class="table table-striped table-bordered table-hover" id="dynamic_field">
        <div>
        <caption><h1><b><u>Branch Wise Empolyee_id with Bank Account No</u></b></h1></caption>
        <thead>
        <tr>
            <th>Branch Code</th>
            <th>EmpId</th>
            <th>Name</th>
            <th>Salary AccountNo</th>
            <th>PIMS_No</th>
            <th>Designation</th>
            <th>Basic</th>
            <th>PF(Emp_Contribution)</th>
            <th>PF(Bank_Contribution)</th>

        </tr>
        </thead>
        <tbody>
        <?php $sum = 0;
        while (($row = oci_fetch_array($s, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
            echo "<tr>";
//        foreach ($row as $item) {
//            echo "  <td>" . ($item !== null ? htmlentities($item, ENT_QUOTES) : "&nbsp;") . "</td>";
//        }

            echo"<td><input  name=\"br_code[]\" value=" .$row['BR_CODE'] ."  readonly></td>";
            echo"<td><input  name=\"emp_id[]\" value=" .$row['EMP_ID'] ."  readonly ></td>";
            echo"<td>".$row['EMP_NAME']."</td>";

            $accountNO = $row['ACCOUNT_NO'];
            if (empty($accountNO)) {
                echo"<td><input  name=\"account_no[]\" value=" ." ></td>";
            } else {
                echo"<td><input  name=\"account_no[]\" value=" .$row['ACCOUNT_NO'] ." readonly  ></td>";
            }
            echo"<td><input  name=\"pims[]\" value=" .$row['PIMS'] ." readonly ></td>";
            echo"<td>" .$row['DESIGNATIOM'] ."</td>";
            echo"<td><input  name=\"sal[]\" value=" .$row['SAL'] ." ></td>";
            echo"<td><input  name=\"pf[]\" value=" .$row['SAL']*0.1 ." ></td>";
            echo"<td><input  name=\"bpf[]\" value=" .$row['SAL']*0.0833 ." ></td>";

            $sum=$sum+$row['SAL']*0.1;
//        echo "  <td>" .$row['BR_CODE'] ."</td>";
//        echo "  <td>" .$row['BRN_NAME'] ."</td>";
//        echo "  <td>" .$row['EMP_ID'] ."</td>";
//        echo "  <td>" .$row['EMP_NAME'] ."</td>";
//        echo "  <td>" .$row['ACCOUNT_NO'] ."</td>";
//        echo "  <td>" .$row['PIMS'] ."</td>";
//        echo "  <td>" .$row['designatiom'] ."</td>";
//        echo "  <td>" .$row['SAL'] ."</td>";
//        echo "  <td>" .$row['SAL']* 0.1 ."</td>";

        }
        echo"<td><button type=\"button\" name=\"add\" id=\"add\" class=\"btn btn-success\">Add More</button></td>";
        echo "</tr>";
        ?>
       </div>
        <tr id="Total">
            <td colspan="7">Total PF</td>
            <td>  <input type="text" name="totalpf" value="<?php echo $sum?>" >  </td>
        </tr>
        </tbody>
    </table>
    <input type="submit" style="margin: 0 auto;display: block; width:200px; " name="btn"  value="Save">
</form>>
</div>
<?php
require_once 'vendor/autoload.php';
use App\classes\Student;
use App\classes\Login;
$student = new Student();
$login = new Login();
//echo "Ora".$r;
$link = mysqli_connect('localhost','root','','test');
//$sql = "SELECT * FROM hr  WHERE  brcode = 1001";
//$result = $student->getStudentInfoById11($_GET['brcode']);
//$result = $student->$sql;
//getEmpInfoById($sr)
//$data = mysqli_fetch_assoc($result);
$message= '';

if(isset($_POST['btn'])){
    // for sal-pf update $message = $student->updateStudentInfo1();
    //$message = $row->saveEmpSalPfById();
    $length = count($_POST['pims']);
    echo $length;
    $month = date("Y-m-d");
    //echo "Date " .$month;
    $link = mysqli_connect('localhost','root','','test');
    /*   $qr =  query(" select fn_check_sal ('$br','$month')");
       print_r ($qr);*/
    $br=$_SESSION['brcode']; echo "sp br".$br;
    $sql= "select fn_check_sal ('$br','$month') as myresult";
    // $sql = 'CALL GetCustomers()';
    // call the stored procedure
    $q = mysqli_query($link,$sql);
    $row = mysqli_fetch_assoc($q);
    $myresult = $row['myresult'];
    echo "RES ".$myresult;
    if ($row['myresult'] == "Not Exists") {
        echo "IN IF";
        for ($x = 0; $x < $length; $x++) {
            $brCode = $_POST['br_code'][$x];
            $empid = $_POST['emp_id'][$x];
            $pims = $_POST['pims'][$x];
            $accno = $_POST['account_no'][$x];
            $sal = $_POST['sal'][$x];
            $pf = $_POST['pf'][$x];
            $bpf = $_POST['bpf'][$x];
            //   $date = $_GET['date'];
            date_default_timezone_set('Asia/dhaka');


            $sql = "  INSERT INTO `sal` (`brcode`, `empid`, `accno`,`pimsno`, `month`,`basic`,`pf`,`bpf`) 
 VALUES ('$brCode','$empid' ,'$accno','$pims','$month','$sal','$pf','$bpf');";
            mysqli_query($link, $sql);
        }
    } else {
        echo "IN else";
    }

//        echo "sql=".$sql;
//        //  $sql = "INSERT INTO students(student_name,mobile_no,email_address) VALUES ('$student_name','$mobile_no','$email_address')";
//        if(mysqli_query($link,$sql)){
//            header('location:successful.php');
//            return 'Branch Data saved successfully';
//        } else {
//            die('Query Problem'.mysqli_error($link));
//        };

}
if(isset($_GET['logout'])){
    $login->logout();
}
?>
<script>
    $(document).ready(function(){
        var i=1;
        $('#add').click(function(){
            i++; $('#Total').remove();
            $('#dynamic_field').append('<tr id="row'+i+'">' +
                '<td><input type="text" name="br_code[]" placeholder="Branch Code" class="form-control name_list" /></td>' +
                '<td><input type="text" name="emp_id[]" placeholder="Emp id" class="form-control name_list" /></td>' +
            '<td><input type="text" name="emp_name[]" placeholder="Emp Name" class="form-control name_list" /></td>' +
            '<td><input type="text" name="account_no[]" placeholder="Account no" class="form-control name_list" /></td>' +

                '<td><input type="text" name="pims[]" placeholder="pims no" class="form-control name_list" /></td>' +
              '<td><input type="text" name="designation[]" placeholder="Designation" class="form-control name_list" /></td>' +
              '<td><input type="text" name="sal[]" placeholder="Basic" class="form-control name_list" /></td>' +
              '<td><input type="text" name="pf[]" placeholder="Provident fund Own Contribution" class="form-control name_list" /></td>' +
              '<td><input type="text" name="bpf[]" placeholder="PF bank Contribution" class="form-control name_list" /></td>' +
                '<td><button type="button" name="remove" id="'+i+'" class="btn btn-danger btn_remove">Remove</button></td></tr>'+
                '<tr id="Total"><td colspan="7">Total PF</td><td>  <input type="text" name="totalpf" value="<?php echo $sum?>" > </td></tr>'



            );
        });

        $(document).on('click', '.btn_remove', function(){
            var button_id = $(this).attr("id");
            $('#row'+button_id+'').remove();
        });

        $('#submit').click(function(){
            $.ajax({
                url:"SalTest.php",
                method:"POST",
                data:$('#add_name').serialize(),
                success:function(data)
                {
                    alert(data);
                    $('#add_name')[0].reset();
                }
            });
        });

    });
</script>


<script>
    function calculate() {

        var $row = $(this).closest('tr')
        var $basic = $row.find("#basic").text();
        alert( $basic);
        // var tr = e.target.parentNode.children()[4];
        // var txtFirstNumberValue = document.getElementById('txt1').value;
        // var $result = parseFloat($basic)*.10;
        // alert($result)
        /*if (!isNaN(result)) {
            document.getElementById('txt2').value = result;
        }*/
    }
</script>
