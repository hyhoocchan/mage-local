<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>SCAN DATA</title>
      	<link rel="stylesheet" type="text/css" href="css/style.css" />
      	<script src="js/jquery-1.4.2.min.js"></script>
    </head>
    <body>
    <div id="wrap">
    	<div id="header">
        	<div class='sologan'>SCAN DATA IN DB</div>            
        </div>
    	<div id="main_content">
    		<div id="resultscandata"></div>
    		<table id="scantable">
    			<tr>
    				<td>Insert Data</td>
    				<td><input type="text" name ="scandata" size="70"/></td>
    			</tr>
    			<tr>
    				<td>Scan Type</td>
    				<td><input type="checkbox" name ="scantype"/> like '%value%'</td>
    			</tr>
    			<tr>
    				<td></td>
    				<td><input class="btnScan" type="button" name ="btnScan" value="Scan Data"/></td>
    			</tr>
    		</table>
    	</div>	
        	<!-- End Main Content -->
    	<div id="footer">
        	<h3>Copy right @ xXx</h3>
        </div> 
        <!-- End Footer -->
    </div>
   <script>
$(document).ready(function(){
	$("#resultscandata").ajaxSend(function(){
		$(this).html("Loading ...");
	});
	//Button Scan Event
	$("input[name=btnScan]:button").click(function(){
		var inputdata = $("input[name=scandata]:text").val();
		var scantype  = $("input[name=scantype]:checkbox:checked").val();
		if (inputdata == "") {alert("Input Data");return};
		$.post(
				'scan.php',
				{
					'scandata': inputdata,
					'scantype': scantype
				},
				function(data){
					$("#resultscandata").html(data);
				}
		);
	});      
});
   </script>
    </body>
</html>
