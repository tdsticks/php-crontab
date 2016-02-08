<?php

class CronParser {

	function __construct()
	{
		//echo __METHOD__;
	}


	function parse_crontab()
	{
		//echo __METHOD__;

		// Sun=0,Mon=1,Tue=2,Wed=3,Thu=4,Fri=5,Sat=6
		// m h dom mon dow command

		// Get the raw crontab text
		//$raw_ct 								= shell_exec('crontab -l');
		$raw_ct 								= file("crontab.txt");
		//print_r($raw_ct);

		//exit();

		// Split up the raw crontab text into lines
		//$raw_ct_ary 							= explode("\n", $raw_ct);
		//print_r($raw_ct_ary);

		$ct_ary  								= array();

		// Set the search path for email app
		$search_path							= "/var/www/html/";
		$search_path_2							= "/var/data/";

		//
		// Loop through the raw crontab data, line by line
		//
		foreach($raw_ct as $raw_line)
		{
			//print_r($raw_line); echo "<br>";

			$schedule 							= array();
			$command 							= array();
			$project 							= array();
			$comment 							= array();

			$search_path_choice 				= stripos( $raw_line, $search_path, 0);
			$search_path_choice_2 				= stripos( $raw_line, $search_path_2, 0);

			// search for email app path in crontab text
			if ( ($search_path_choice !== FALSE) || ($search_path_choice_2 !== FALSE) )
			{
				//print_r($raw_line); echo "<br>";

				// Trim off any commented out lines in case that happens
				$ct_line 					= ltrim($raw_line, "#"); // remove the hash if it exists at beginning
				//$ct_line 					= $raw_line; // remove the hash if it exists at beginning

				// Split apart each acceptable line
				$ct_line_ary 				= explode(" ", $ct_line);
				//print_r($ct_line_ary); echo "<br>";


				//
				// Check to see if the cron is commented out or not
				//



				$commented_out 				= FALSE;
				if ( strpos( $raw_line, "#" ) === 0 ) {
					$commented_out 			= TRUE;
				}
				//echo "commented_out: " . strpos( $raw_line, "#" ) . " " . $commented_out;
				//echo "commented_out: " . $commented_out;


				//
				// Choose the correct command path based on the search paths
				//
				$selected_search_path 		= "";

				if ($search_path_choice !== FALSE) {
					$selected_search_path 	= $search_path;
				} else if ($search_path_choice_2 !== FALSE) {
					$selected_search_path 	= $search_path_2;
				}



				// Condition for bkup scripts
				if (!isset($ct_line_ary[7])) {
					//print_r($raw_line); echo "<br>";
					$ct_line_ary[7] 		= "";
				}


				//
				// Get the project
				//
				$get_project_replace 		= str_replace($selected_search_path, "", $ct_line_ary[6]);
				$get_project_explode		= explode("/", $get_project_replace);
				$project 					= $get_project_explode[0];

				// Factor in TOMS
				if ( stripos($ct_line_ary[7], "_toms") !== FALSE ) {
					$project 				= "Welcome TOMS";
				}
				//echo $project; echo "<br>";



				// Condition for ET_to_Audi crons
				if ($project == 'et_to_audi') {
					$split_path 			= explode( "/", $ct_line_ary[6] );

					//$ct_line_ary[9] 		= $ct_line_ary[7].$ct_line_ary[10];
					$ct_line_ary[7] 		= $split_path[ count($split_path)-2 ];
					$ct_line_ary[8] 		= $split_path[ count($split_path)-1 ];
				}



				//
				// Get the schedule
				//
				$schedule 					= array(
													"Minute"		=> $ct_line_ary[0],
													"Hour" 			=> $ct_line_ary[1],
													"DayOfMonth" 	=> $ct_line_ary[2],
													"Month" 		=> $ct_line_ary[3],
													"DayOfWeek" 	=> $ct_line_ary[4]
				);
				//print_r($schedule); echo "<br>";


				$path 						= str_replace( $selected_search_path, "", $ct_line_ary[6]);
				//echo $path; echo "<br>";

				//
				// Get the command
				//
				$command 					= array(
													"exec" 			=> $ct_line_ary[5],
													"path" 			=> $path,
													"class" 		=> $ct_line_ary[7]
				);
				$command['enabled'] 		= $commented_out;

				// Add method if it exists
				if ( isset($ct_line_ary[8]) ) {
					$command['method'] 		= $ct_line_ary[8];
				}
				//print_r($command); echo "<br>";

				//echo "<br>";
				//echo "<br>";

				//
				// Get the comments
				//
				/*
				comment 					= "";

				// If comments exist, loop remaining array and add to string var
				if ( isset($ct_line_ary[9]) )
				{
					for($i=9; $i<count($ct_line_ary); $i++) {
						//echo $i;
						$comment 			.= $ct_line_ary[$i] . " ";
					}
					//echo $comment; echo "<br>";
				}
				*/


				//
				// Formulate the crontab array
				//
				$project_ary 				= array(
													"schedule" => $schedule,
													"command" => $command,
													"comment" => $comment
				);
				//print_r($project_ary); echo "<br>";

				$ct_ary[$project][] 		= $project_ary;


			} // End if stripos raw_line

		} // End foreach raw_ct_ary

		//print_r($ct_ary);

		array_multisort($ct_ary);

		return $ct_ary;
	} // End parse_crontab()


	function display_crontab( $p_crontab )
	{
		//echo __METHOD__;

		/*foreach ($p_crontab as $program => $p_data) {

			//print_r($p_data); echo "<br/>";

			foreach ($p_data as $d_key => $d_val) {

				print_r($d_val); echo "<br/>";
			}
		}*/


		//exit;


		echo "<html>";
			echo "<head>";
				echo "<style>";
					//echo "table,h3 { padding: 15px };";
					echo ".program,h3 { padding-left: 20px; padding-right: 20px; padding-top: 0px; padding-bottom: 0px };";
					echo "h3 { padding-left: 20px; padding-top: 0px; padding-bottom: 0px; };";
				echo "</style>";

				// Latest compiled and minified CSS
				echo '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">';

				// Optional theme
				echo '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">';

				// jQuery
				echo '<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>';

				// Latest compiled and minified JavaScript
				echo '<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>';
			echo "</head>";

		echo "<body>";

		echo "<nav class='navbar navbar-default'>";
			echo "<div class='container-fluid'>";
				echo "<div class='navbar-header'>";
					echo "<a class='navbar-brand'>Email Proc</a>";
				echo "</div>";
				echo "<div>";
					echo "<ul class='nav navbar-nav'>";
						echo "<li class=''><a href='#'>Scheduled Crons</a></li>";
					echo "</ul>";
				echo "</div>";
			echo "</div>";
		echo "</nav>";

		$dow_ary 							= array('Sun','Mon','Tue','Wed','Thu','Fri','Sat');
		$mon_ary  							= array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');

		foreach ($p_crontab as $program => $p_data) {

			//print_r($p_data); echo "<br/>";

			echo "<h3>".ucwords($program)."</h3>";

			echo "<div class='program'>";

			//print_r( $p_data );
			echo "<table class='table table-bordered table-condensed'>";
			echo "<thead>";
				echo "<tr>";
					echo "<th>"."Day of Week"."</th>";
					echo "<th>"."Month"."</th>";
					echo "<th>"."Time"."</th>";
					echo "<th>"."Command"."</th>";
					echo "<th>"."Path"."</th>";
					echo "<th>"."Class"."</th>";
					echo "<th>"."Method"."</th>";
					//echo "<th>"."Comments"."</th>";
				echo "</tr>";
			echo "</thead>";

			echo "<tbody>";
			foreach ($p_data as $d_key => $d_val) {

				//print_r($d_val['schedule']); //echo "<br/>";

				$Minute 					= $d_val['schedule']['Minute'];
				$Hour 						= $d_val['schedule']['Hour'];
				$AMPM 						= "am";
				$DayOfMonth 				= $d_val['schedule']['DayOfMonth'];
				$Month 						= $d_val['schedule']['Month'];
				$DayOfWeek 					= $d_val['schedule']['DayOfWeek'];

				// Create the Day of Week string based on crontab
				$dis_dow 					= $this->create_formatted_string($dow_ary, $DayOfWeek, 'Everyday');
				$dis_mon 					= $this->create_formatted_string($mon_ary, $Month, 'Every month');


				//
				// Format the time str
				//
				$dis_time 					= "";
				$explode_hour 				= explode(",", $Hour);

				foreach ($explode_hour as $ex_hr) {
					$min 					= $Minute;

					if ($ex_hr > 12) {
						$ex_hr 				= $ex_hr - 12;
						$AMPM 				= "pm";
					} else if ($ex_hr == 0) {
						$ex_hr 				= 12;
						$AMPM 				= "am";
					}

					if (strlen($min<2)) {
						$min 				= $min . "0";
					}

					$dis_time 				.= $ex_hr.":".$min.$AMPM.", ";
				}
				$dis_time 					= substr($dis_time, 0, -2);
				//echo $dis_time; echo "<br/>";


				$exec  						= $d_val['command']['exec'];
				$path  						= $d_val['command']['path'];
				$class  					= $d_val['command']['class'];
				$method 					= "";
				//$comment 					= "";

				if ( isset($d_val['command']['method']) ) {
					$method  				= $d_val['command']['method'];
				}
				/*if ( isset($d_val['comment']) ) {
					$comment  				= $d_val['comment'];
				}*/


				//$table_class 				= ($d_val['command']['enabled']) ? 'danger' : 'success';
				if ($d_val['command']['enabled'] != 1) {
					$table_class 			= 'success';
				} else {
					$table_class 			= 'danger';
				}
				//

				echo "<tr class='$table_class'>";
					echo "<td>".$dis_dow."</td>";
					echo "<td>".$dis_mon."</td>";
					echo "<td>".$dis_time."</td>";
					echo "<td>".$exec."</td>";
					echo "<td>".$path."</td>";
					echo "<td>".$class."</td>";
					echo "<td>".$method."</td>";
					//echo "<td>".$comment."</td>";
				echo "</tr>";
			}
			echo "</tbody>";
			echo "</table>";

			echo "</div>";

		} // End foreach p_crontab

		echo "</body>";
		echo "</html>";
	} // End display_crontab()


	function create_formatted_string ( $date_ary, $date_val, $catch_all_str ) {
		//
		// Create the Day of Week based on crontab
		//
		$dis_str 					= "";

		if ($date_val == '*') {
			$dis_str 				= $catch_all_str;

		} else if ( strpos($date_val, ',') !== False ) {
			$explode_comma 			= explode(",", $date_val);
			//print_r($explode_comma);

			foreach ($explode_comma as $comma) {
				//echo $comma;
				if ( strpos($comma, '-') !== False ) {
					$explode_dash = explode("-", $comma);

					foreach ($explode_dash as $dash) {
						//echo $dash; echo "<br/>";
						$dis_str 	.= $date_ary[ $dash ] . ",";
					}
				} else {
					//echo $comma; echo "<br/>";
					$dis_str 		.= $date_ary[ $comma ] . ",";
				}
			}

		} else {
			$dis_str 				= $date_ary[ $date_val ];
		}
		$dis_str = rtrim($dis_str, ",");

		return $dis_str;
	}

} // End CronParser class


$new_cron_parser 								= new CronParser();
$parsed_crontab 								= $new_cron_parser->parse_crontab();
$new_cron_parser->display_crontab( $parsed_crontab );