<?php

// Globals
global $post;
global $wpdb;
global $camp_general;
global $post_id;
global $camp_options;
global $post_types;
global $camp_post_category;

?>

<div class="TTWForm-container" dir="ltr">
    <div class="TTWForm">
        <div class="panes">

            <p>Now you can use [gpt] shortcode on the post template above, here are examples:-<br>

            <ol>
                <li><strong>[gpt]Summarize this content to 100 words [matched_content][/gpt]</strong>

                    <p>This should summarize the content of the article and return only 100 words</p>

                </li>
                <li><strong>[gpt]Write an article about [original_title] in French[/gpt]</strong>

                    <p>This should write an article in French language about the title</p>


                </li>
                <li><strong>[gpt]rewrite this title [original_title][/gpt]</strong>

                    <p>This should rewrite the title</p>

                </li>
                <li><strong>[gpt]rewrite this content and keep HTML tags [matched_content][/gpt]</strong>

                    <p>This should rewrite the content and keep the HTML tags</p>

                 </li>



            </ol>

            </p>

            <!-- checkbox use openrouter instead of openai -->
            <div class="field f_100">
                <div class="option clearfix">
                    <input data-controls="openrouter_model_field" data-controls-r="openai_model_field" name="camp_options[]" value="OPT_USE_OPENROUTER" type="checkbox">
                    <span class="option-title">
                        Use OpenRouter instead of OpenAI
                    </span>
                    <br>
                    <div class="description">Enable if you want to use OpenRouter instead of OpenAI to use any model like Google gemini/Claude etc.</div>
                </div>

                <!-- openrouter model text field -->
                <div id="openrouter_model_field" class="field f_100">
                    <label>
                        OpenRouter Model (Optional)
                    </label>
                    <input name="cg_openrouter_model" value="<?php echo isset($camp_general['cg_openrouter_model']) ? $camp_general['cg_openrouter_model'] : '' ?>" type="text">
                    <div class="description">Enter the OpenRouter model name here, visit <a href="https://openrouter.io/models" target="_blank">OpenRouter Models</a> to get the model name. example: google/gemini. <br><br>*If left empty,defaults to the model added to the settings page</div>
                </div>


                <div class="clear"></div>
            </div>
            

            <!-- model field -->
            <div  class="field f_100" id = "openai_model_field">

                <!-- model selection field -->
                 <label>
                            OpenAI Model
                        </label>

                        <!-- model selection field gpt3.5-turbo, 	gpt-4, gpt-4-0314, gpt-4-32k, gpt-4-32k-0314, gpt-3.5-turbo, gpt-3.5-turbo-0301 -->
                        <select name="cg_openai_model">
                            <option value="gpt-4o-mini" <?php echo isset($camp_general['cg_openai_model']) && $camp_general['cg_openai_model'] == 'gpt-4o-mini' ? 'selected' : '' ?>>gpt-4o-mini (128k) (BEST)</option>
                            <option value="gpt-4o" <?php echo isset($camp_general['cg_openai_model']) && $camp_general['cg_openai_model'] == 'gpt-4o' ? 'selected' : '' ?>>gpt-4o (128k) (NEW) (SMARTEST) (Output limited to 4k tokens)</option>    
                            
                            <option value="gpt-4" <?php echo isset($camp_general['cg_openai_model']) && $camp_general['cg_openai_model'] == 'gpt-4' ? 'selected' : '' ?>>gpt-4 (OLD) (Up to Sep 2021)                            </option>
                            <option value="gpt-4-turbo" <?php echo isset($camp_general['cg_openai_model']) && $camp_general['cg_openai_model'] == 'gpt-4-turbo' ? 'selected' : '' ?>>gpt-4-turbo (128k)</option>
                            <option value="gpt-4-turbo-preview" <?php echo isset($camp_general['cg_openai_model']) && $camp_general['cg_openai_model'] == 'gpt-4-turbo-preview' ? 'selected' : '' ?>>gpt-4-turbo-preview (128k)</option>
                            <option value="gpt-4-0613" <?php echo isset($camp_general['cg_openai_model']) && $camp_general['cg_openai_model'] == 'gpt-4-0613' ? 'selected' : '' ?>>gpt-4-0613 (OLD)</option>
                            <option value="gpt-4-0314" <?php echo isset($camp_general['cg_openai_model']) && $camp_general['cg_openai_model'] == 'gpt-4-0314' ? 'selected' : '' ?>>gpt-4-0314 (OLD)</option>

                            <option value="gpt-3.5-turbo" <?php echo isset($camp_general['cg_openai_model']) && $camp_general['cg_openai_model'] == 'gpt-3.5-turbo' ? 'selected' : '' ?>>gpt-3.5-turbo</option>
                            
                            

                        </select>
                        <div class="description">Model gpt-4o-mini is affordable and intelligent small model for fast, lightweight tasks.<br> GPT-4o is the high-intelligence flagship model for complex, multi-step tasks</div>

                        <br>

                <div class="clear"></div>
            </div>

            <!-- checkbox field to set the post status to pending if openai prompt failed -->
            <div class="field f_100">
                <div class="option clearfix">
                    <input name="camp_options[]" value="OPT_OPENAI_PENDING" type="checkbox">
                    <span class="option-title">
                        Set post status to pending if OpenAI prompt failed
                    </span>
                    <br>
                    <div class="description">Enable if you want the article post status to be set to pending if the gpt3 prompt failed for any reason</div>
                </div>
                <div class="clear"></div>
            </div>

            <div class="field f_100">
                    <div class="option clearfix">
                        <input data-controls="wp_automatic_openai_advanced" name="camp_options[]" value="OPT_OPENAI_CUSTOM" type="checkbox">
                        <span class="option-title">
                            Modify OpenAI call parameters (advanced)
                        </span>
                    </div>

                    <div id="wp_automatic_openai_advanced" class = "field f_100">


                        <!-- temprature field -->
                        <label for="field6">
                            Temperature (Optional)(Dangerous)
                        </label>
                        <input name="cg_openai_temp" value="<?php echo isset($camp_general['cg_openai_temp']) ? $camp_general['cg_openai_temp'] : '' ?>" type="text">
                        <div class="description">What sampling temperature to use, between 0 and 2. Higher values like 0.8 will make the output more random, while lower values like 0.2 will make it more focused and deterministic. Defaults to 1<br><br>Tests showed that setting this value to something high makes the request processing time go from 30 seconds to more than 5 minutes, better leave as-is.</div>

                        <br>



                        <!-- top_p field -->
                        <label for="field6">
                            Top_p (Optional)
                        </label>
                        <input name="cg_openai_top_p" value="<?php echo isset($camp_general['cg_openai_top_p']) ? $camp_general['cg_openai_top_p'] : '' ?>" type="text">
                        <div class="description">An alternative to sampling with temperature, called nucleus sampling, where the model considers the results of the tokens with top_p probability mass. So 0.1 means only the tokens comprising the top 10% probability mass are considered.

We generally recommend altering this or temperature but not both. Defaults to 1.</div>

                        <br>
                        <!-- presence_penalty field -->
                        <label>
                            Presence_penalty (Optional)
                        </label>
                        <input name="cg_openai_presence_penalty" value="<?php echo isset($camp_general['cg_openai_presence_penalty']) ? $camp_general['cg_openai_presence_penalty'] : '' ?>" type="text">
                        <div class="description">Number between -2.0 and 2.0. Positive values penalize new tokens based on whether they appear in the text so far, increasing the model's likelihood to talk about new topics. Defaults to 0.</div>
                        <br>

                        <!-- frequency_penalty field -->
                        <label>
                            Frequency_penalty (Optional)
                        </label>
                        <input name="cg_openai_frequency_penalty" value="<?php echo isset($camp_general['cg_openai_frequency_penalty']) ? $camp_general['cg_openai_frequency_penalty'] : '' ?>" type="text">
                        <div class="description">Number between -2.0 and 2.0. Positive values penalize new tokens based on their existing frequency in the text so far, decreasing the model's likelihood to repeat the same line verbatim. Defaults to 0.</div>

                        <!-- Fine tuned model -->
                        <br>
                        <label>
                            Fine tuned model (Optional)
                        </label>
                        <input name="cg_openai_fine_tuned_model" value="<?php echo isset($camp_general['cg_openai_fine_tuned_model']) ? $camp_general['cg_openai_fine_tuned_model'] : '' ?>" type="text">
                        <div class="description">If you have a fine tuned model, you can use it here, if you do not have one, leave this field empty.</div>

                        <!-- Dalle 3 image size select Must be one of 1024x1024, 1792x1024, or 1024x1792 -->
                        <br>
                        <label>
                            Dalle 3 image size (Optional)
                        </label>
                        <select name="cg_openai_dalle_image_size">
                            <option value="1024x1024" <?php echo isset($camp_general['cg_openai_dalle_image_size']) && $camp_general['cg_openai_dalle_image_size'] == '1024x1024' ? 'selected' : '' ?>>1024x1024</option>
                            <option value="1792x1024" <?php echo isset($camp_general['cg_openai_dalle_image_size']) && $camp_general['cg_openai_dalle_image_size'] == '1792x1024' ? 'selected' : '' ?>>1792x1024</option>
                            <option value="1024x1792" <?php echo isset($camp_general['cg_openai_dalle_image_size']) && $camp_general['cg_openai_dalle_image_size'] == '1024x1792' ? 'selected' : '' ?>>1024x1792</option>
                        </select>


                    </div>


                <div class="clear"></div>
            </div>


        </div>
    </div>
</div>