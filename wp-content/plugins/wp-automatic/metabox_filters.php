<?php

// Globals
global $post;
global $wpdb;
global $camp_general;
global $post_id;
global $camp_options;
global $post_types;

global $camp_post_exact;
global $camp_post_execlude;

?>

<div class="TTWForm-container" dir="ltr">
	<div class="TTWForm">
		<div class="panes">
		
					   <div id="exact_match" class="field f_100">
               <div class="option clearfix">
                    
                    <input name="camp_options[]" id="exact_option" value="OPT_EXACT" type="checkbox">
                    <span class="option-title">
							Only post the article if it contains one or more of specific words
                    </span>
                    <br>
                    
		            <div id="exact_match_c" class="field f_100">
		               <label for="field6">
		                    Exact match words (one word per line )
		               </label>
		               
		            	<textarea name="camp_post_exact" ><?php   echo $camp_post_exact?></textarea>
		            	
		            	<div class="option clearfix">
			            	<input name="camp_options[]" id="exact_option" value="OPT_EXACT_AFTER" type="checkbox">
		                    <span class="option-title">
									Apply to final content after filling the template (by default applies to content just fetched from the source)
		                    </span>
	                    </div>
	                    
	                    <div class="option clearfix">
		                    <input name="camp_options[]"  value="OPT_EXACT_TITLE_ONLY" type="checkbox">
		                    <span class="option-title">
									Only check at the title (by default the title and content get checked)
		                    </span>
	                    </div>
	                    
	                    <div class="option clearfix">
		                    <input name="camp_options[]"  value="OPT_EXACT_STR" type="checkbox">
		                    <span class="option-title">
									Exact string match (by default REGEX word match is used)
		                    </span>
	                    </div>
	                    
	                    <div class="option clearfix">
		                    <input name="camp_options[]"  value="OPT_EXACT_ALL" type="checkbox">
		                    <span class="option-title">
									Post must contain all words (By default if any word exists)
		                    </span>
	                    </div>
		            	
		            </div>
		            
               </div>
		 </div>
		 
		 
		 		
		 <div id="exact_execlude" class="field f_100">
               <div class="option clearfix">
                    
                    <input name="camp_options[]" id="execlude_option" value="OPT_EXECLUDE" type="checkbox">
                    <span class="option-title">
							Skip the post if it contains one or more of these words
                    </span>
                    <br>
                    
		            <div id="exact_execlude_c" class="field f_100">
		               <label for="field6">
		                    banned words (one word per line )
		               </label>
		               
		            	<textarea name="camp_post_execlude" ><?php   echo $camp_post_execlude ?></textarea>
		            	
		            		<div class="option clearfix">
			            		<input name="camp_options[]" id="exact_option" value="OPT_EXECLUDE_AFTER" type="checkbox">
		                    <span class="option-title">
									Apply to final content after filling the template (by default applies to content just fetched from the source)
		                    </span>
	                    </div>
	                    
	                    <div class="option clearfix">
			            		<input name="camp_options[]" id="exact_option" value="OPT_EXECLUDE_TITLE_ONLY" type="checkbox">
		                    <span class="option-title">
									Only check at the title (By default, it checks the title and the content)
		                    </span>
	                    </div>
	                    
	                    <div class="option clearfix">
		                    <input name="camp_options[]"  value="OPT_EXCLUDE_EXACT_STR" type="checkbox">
		                    <span class="option-title">
									Exact string match (by default REGEX word match is used)
		                    </span>
	                    </div>
		            	
		            </div>
		            
               </div>
		 </div>
		 
		 <div class="field f_100">
               <div class="option clearfix">
                    
                    <input name="camp_options[]" data-controls="exact_match_regex_c" value="OPT_EXACT_REGEX" type="checkbox">
                    <span class="option-title">
							Only post the item if it matches one of these specific REGEX (Regular expressions)
                    </span>
                    <br>
                    
		            <div id="exact_match_regex_c" class="field f_100">
		               <label for="field6">
		                   Escapped regular expression without delimiter 
		               </label>
		               
		            	<textarea name="cg_camp_post_regex_exact" ><?php   echo @$camp_general['cg_camp_post_regex_exact']?></textarea>
		            	<div class="description">*default delimiter is {} <br>*one REGEX per line</div>
 		            </div>
		            
               </div>
		 </div>
		 
		 <div class="field f_100">
               <div class="option clearfix">
                    
                    <input name="camp_options[]" data-controls="exclude_match_regex_c" value="OPT_EXCLUDE_REGEX" type="checkbox">
                    <span class="option-title">
							Skip the post if the item matches one of these specific REGEX (Regular expressions)
                    </span>
                    <br>
                    
		            <div id="exclude_match_regex_c" class="field f_100">
		               <label for="field6">
		                   Escapped regular expression without delimiter 
		               </label>
		               
		            	<textarea name="cg_camp_post_regex_exclude" ><?php   echo @$camp_general['cg_camp_post_regex_exclude']?></textarea>
		            	<div class="description">*default delimiter is {} <br>*one REGEX per line</div>
 		            </div>
		            
               </div>
		 </div>
		 
		 
		 <div class="field f_100">
               <div class="option clearfix">
                    
                    <input name="camp_options[]" data-controls="exclude_match_criteria_c" value="OPT_CRITERIA" type="checkbox">
                    <span class="option-title">
							Skip the post if the a specific criteria applies to a specific field
                    </span>
                    <br>
                    
		            <div id="exclude_match_criteria_c" class="field f_100">
		              
		               
		               <div class="field f_100">
		               
		               		<div class="option clearfix">
						                    
								            <div>
								            
								                <table>
								                		
								                		<tr>
						 		 		 			
						 		 		 			<td>
						 		 		 				Field Name
						 		 		 			</td>
						 		 		 			
						 		 		 			<td>
						 		 		 				Criteria
						 		 		 			</td>
						 		 		 			
						 		 		 			<td>
						 		 		 				Value
						 		 		 			</td>
						 		 		 			
						 		 		 			
						 		 		 			</tr>
								                	
								                	 	<?php 
 		 		 				 
								                	 	$cg_criteria_skip_fields= @$camp_general['cg_criteria_skip_fields'];
								                	 	$cg_criteria_skip_criterias= @$camp_general['cg_criteria_skip_criterias'];
								                	 	$cg_criteria_skip_values = @$camp_general['cg_criteria_skip_values'];
						 		 		 			
								                	 	if(!is_array($cg_criteria_skip_fields)) $cg_criteria_skip_fields= array('');
								                	 	if(!is_array($cg_criteria_skip_criterias)) $cg_criteria_skip_criterias= array('==');
								                	 	if(!is_array($cg_criteria_skip_values)) $cg_criteria_skip_values= array('');
						 		 		 			
						 		 		 			$i=0;
						 		 		 			foreach ($cg_criteria_skip_fields as $cg_criteria_skip_field){
						 		 		 				
						 		 		 				if(  true ) {
						 		 		 			?>
						 		 		 			
						 		 		 			
						 		 		 			
						 		 		 			<tr>
						 		 		 				
						 		 		 				
						 		 		 				
						 		 		 				<td  style="padding-right:10px;width:130px;vertical-align: top;" >
						 		 		 				 		<input  class="no-unify"  value="<?php  echo wp_automatic_htmlentities( $cg_criteria_skip_field ,ENT_COMPAT, 'UTF-8') ?>" name="cg_criteria_skip_fields[]" type="text">
						 		 		 				</td>
						 		 		 				
							 		 		 				<td style="padding-right:8px; vertical-align: top;">
							 		 		 					 <select class="no-unify"   name="cg_criteria_skip_criterias[]">
																	 <option value="==" <?php @wp_automatic_opt_selected('==',$cg_criteria_skip_criterias[$i]) ?>>==</option>
																	 <option value="greater" <?php @wp_automatic_opt_selected('greater',$cg_criteria_skip_criterias[$i]) ?>>	&gt</option>
																	 <option value="less" <?php @wp_automatic_opt_selected('less',$cg_criteria_skip_criterias[$i]) ?>>	&lt</option>
																	 <option value="contains" <?php @wp_automatic_opt_selected('contains',$cg_criteria_skip_criterias[$i]) ?>>Contains</option>
																	 <option value="length_greater" <?php @wp_automatic_opt_selected('length_greater',$cg_criteria_skip_criterias[$i]) ?>>Chars length &gt</option>
																	 <option value="length_less" <?php @wp_automatic_opt_selected('length_less',$cg_criteria_skip_criterias[$i]) ?>>Chars length &lt</option>
																</select>
							 		 		 				</td>
						
						 		 		 				<td  style="padding-right:10px;width:130px" >
						 		 		 						<textarea style="height:70px" class="no-unify" name="cg_criteria_skip_values[]" ><?php  echo wp_automatic_htmlentities( $cg_criteria_skip_values[$i] ,ENT_COMPAT, 'UTF-8')  ?></textarea>
						 		 		 				
						 		 		 				</td>
						 		 		 				
						 		 		 				<td    style="padding-left:10px;padding-top:8px;" >
						 		 		 					 <button  title="Duplicate rule"  class="duplicator" >+</button>
						 		 		 				</td>
						 		 		 				
						 		 		 				<td    style="padding-left:5px;padding-top:8px;" >
						 		 		 					 <button  title="Remove rule"  class="cleaner" >x</button>
						 		 		 				</td>
						 		 		 			
						 		 		 			</tr>
 		 		 		
 		 		 						<?php 
 		 		 						
 		 		 									$i++ ; 
						 		 		 			
						 		 		 				}	
						 		 		 } ?>
								                	
								                	
								              
								                	
								                
								                </table>
								                
								                 
							                    
								            </div>
								            
						               </div>
						               
									   <input name="camp_options[]"  value="OPT_CRITERIA_ALL" type="checkbox">
										<span class="option-title">
												Skip the post only if all rules applied (default: skip if any rule applied)
										</span>

						               <div class="description">* This option will check returned fields that you pick and skip the post if <strong>ANY</strong> of the set rules applied
								            <br><br>*Copy the field name from below the campaign post template option above
								            <br><br>*<strong>Example1</strong>: we can skip the post if the title contains the word "bad", use this config:<br>[vid_title] <small>--</small> Contains <small>--</small> Bad
								            <br><br>* You can add multiple values in the value field (one per line), the plugin will check every value
								            <br><br>*<strong>Example2</strong>: If we want to skip the youtube video if the author is named "Atef", use this config:<br>[vid_author_title] <small>--</small> == <small>--</small> Atef 
								            
								            
								        </div>
		               
		               </div>
		               
		            	
		            	 
 		            </div>
		            
               </div>
		 </div>
		 
		 
		 <div class="field f_100">
               <div class="option clearfix">
                    
                    <input name="camp_options[]" data-controls="exclude_match_criteria_m_c" value="OPT_CRITERIA_MUST" type="checkbox">
                    <span class="option-title">
							Only import the post if the a specific criteria applies to a specific field 
                    </span>
                    <br>
                    
		            <div id="exclude_match_criteria_m_c" class="field f_100">
		              
		               
		               <div class="field f_100">
		               
		               		<div class="option clearfix">
						                    
								            <div>
								            
								                <table>
								                		
								                		<tr>
						 		 		 			
						 		 		 			<td>
						 		 		 				Field Name
						 		 		 			</td>
						 		 		 			
						 		 		 			<td>
						 		 		 				Criteria
						 		 		 			</td>
						 		 		 			
						 		 		 			<td>
						 		 		 				Value
						 		 		 			</td>
						 		 		 			
						 		 		 			
						 		 		 			</tr>
								                	
								                	 	<?php 
 		 		 				 
								                	 	$cg_criteria_skip_fields_must= @$camp_general['cg_criteria_skip_fields_must'];
								                	 	$cg_criteria_skip_criterias_must= @$camp_general['cg_criteria_skip_criterias_must'];
								                	 	$cg_criteria_skip_values_must = @$camp_general['cg_criteria_skip_values_must'];
						 		 		 			
								                	 	if(!is_array($cg_criteria_skip_fields_must)) $cg_criteria_skip_fields_must= array('');
								                	 	if(!is_array($cg_criteria_skip_criterias_must)) $cg_criteria_skip_criterias_must= array('==');
								                	 	if(!is_array($cg_criteria_skip_values_must)) $cg_criteria_skip_values_must= array('');
						 		 		 			
						 		 		 			$i=0;
						 		 		 			foreach ($cg_criteria_skip_fields_must as $cg_criteria_skip_field_must){
						 		 		 				
						 		 		 				if(  true ) {
						 		 		 			?>
						 		 		 			
						 		 		 			
						 		 		 			
						 		 		 			<tr>
						 		 		 				
						 		 		 				
						 		 		 				
						 		 		 				<td  style="padding-right:10px;width:130px;vertical-align: top;" >
						 		 		 				 		<input  class="no-unify"  value="<?php  echo wp_automatic_htmlentities( $cg_criteria_skip_field_must ,ENT_COMPAT, 'UTF-8') ?>" name="cg_criteria_skip_fields_must[]" type="text">
						 		 		 				</td>
						 		 		 				
							 		 		 				<td style="padding-right:8px; vertical-align: top;">
							 		 		 					 <select class="no-unify"   name="cg_criteria_skip_criterias_must[]">
																	 <option value="==" <?php @wp_automatic_opt_selected('==',$cg_criteria_skip_criterias_must[$i]) ?>>==</option>
																	 <option value="greater" <?php @wp_automatic_opt_selected('greater',$cg_criteria_skip_criterias_must[$i]) ?>>	&gt</option>
																	 <option value="less" <?php @wp_automatic_opt_selected('less',$cg_criteria_skip_criterias_must[$i]) ?>>	&lt</option>
																	 <option value="contains" <?php @wp_automatic_opt_selected('contains',$cg_criteria_skip_criterias_must[$i]) ?>>Contains</option>
																	 <option value="length_greater" <?php @wp_automatic_opt_selected('length_greater',$cg_criteria_skip_criterias_must[$i]) ?>>Chars length &gt</option>
																	 <option value="length_less" <?php @wp_automatic_opt_selected('length_less',$cg_criteria_skip_criterias_must[$i]) ?>>Chars length &lt</option>
																</select>
							 		 		 				</td>
						
						 		 		 				<td  style="padding-right:10px;width:130px" >
						 		 		 						<textarea style="height:70px" class="no-unify" name="cg_criteria_skip_values_must[]" ><?php  echo wp_automatic_htmlentities( $cg_criteria_skip_values_must[$i] ,ENT_COMPAT, 'UTF-8')  ?></textarea>
						 		 		 				
						 		 		 				</td>
						 		 		 				
						 		 		 				<td    style="padding-left:10px;padding-top:8px;" >
						 		 		 					 <button  title="Duplicate rule"  class="duplicator" >+</button>
						 		 		 				</td>
						 		 		 				
						 		 		 				<td    style="padding-left:5px;padding-top:8px;" >
						 		 		 					 <button  title="Remove rule"  class="cleaner" >x</button>
						 		 		 				</td>
						 		 		 			
						 		 		 			</tr>
 		 		 		
 		 		 						<?php 
 		 		 						
 		 		 									$i++ ; 
						 		 		 			
						 		 		 				}	
						 		 		 } ?>
								                	
								                	
								              
								                	
								                
								                </table>
								                
								                 
							                    
								            </div>

											<input name="camp_options[]"  value="OPT_CRITERIA_MUST_ANY" type="checkbox">
											<span class="option-title">
													Approve the post if any rule applies (default: approve if all rules apply)
											</span>
								            
								            <div class="description">* This option will check returned fields that you pick and import the post only if <strong>ALL</strong> set rules applied
								            <br><br>*Copy the field name from below the campaign post template option above
								            <br><br>*<strong>Example1</strong>: We can import the post only if the title contains the word "love", use this config<br>[vid_title] <small>--</small> Contains <small>--</small> Love
								            <br><br>* You can add multiple values in the value field (one per line), the plugin will check every value
								            <br><br>*<strong>Example2</strong>: If we want to only post youtube videos that thier title is greater in lenth than 40 chars, use this config:<br>[vid_title] <small>--</small> Char length > <small>--</small> 40 
								            
								            
								            </div>
								            
						               </div>
		               
		               </div>
		               
		            	
		            
 		            </div>
		            
               </div>
		 </div>		 
		 
		 <div class="field f_100">
		    <div class="option clearfix">
                    <input name="camp_options[]"   value="OPT_FEED_TITLE_SKIP" type="checkbox">
                    <span class="option-title">
							Skip the post if there is there is already a published one with same title in the database
                    </span>
                    <br>
             </div>
         </div>
         
         <div class="field f_100">
         	<div class="option clearfix">
								                    
	                    <input name="camp_options[]"  data-controls="limit_min_length_c"   value="OPT_MIN_LENGTH" type="checkbox">
	                    <span class="option-title">
								Skip posts if shorter than a specific length
	                    </span>
	                    <br>
	                    
			            <div id="limit_min_length_c" class="field f_100">
			               <label>
			                    Minimum number of characters ?
			               </label>
			               
			                <input value="<?php   echo @$camp_general['cg_min_length']   ?>" max="20000" min="0" name="cg_min_length" required="required" class="ttw-range range" id="cg_min_length" type="range">
			               
			            </div>
			            
	               </div>
         </div>
		
		 <div class="field f_100">
         	<div class="option clearfix">
								                    
	                    <input name="camp_options[]"  data-controls="limit_max_length_c"   value="OPT_MAX_LENGTH" type="checkbox">
	                    <span class="option-title">
								Skip posts if longer than a specific length
	                    </span>
	                    <br>
	                    
			            <div id="limit_max_length_c" class="field f_100">
			               <label>
			                    Maximum number of characters ?
			               </label>
			               
			                <input value="<?php   echo @$camp_general['cg_max_length']   ?>" max="20000" min="0" name="cg_max_length" required="required" class="ttw-range range" id="cg_max_length" type="range">
			               
			            </div>
			            
	               </div>
         </div> 
         
         <div class="field f_100">
               <div class="option clearfix">
                    
                    <input name="camp_options[]" data-controls="comment_skip_filter_keys" value="OPT_FILTER_COMMENT" type="checkbox">
                    <span class="option-title">
							Skip the comment if a keyword exists in the comment body or username
                    </span>
                    <br>
                    
		            <div id="comment_skip_filter_keys" class="field f_100">
		               <label for="field6">
		                   Banned keywords (one word per line)
		               </label>
		               
		            	<textarea name="cg_comment_filter_keys" ><?php   echo @$camp_general['cg_comment_filter_keys']?></textarea>
		            	<div class="description">*The plugin will check if this keyword exist on the comment and skip if available. it will also check the username </div>
 		            </div>
		            
               </div>
		 </div>
		
		<div class="clear"></div>
	</div>
</div>
</div>
