/*------------------------- 
Frontend related javascript
-------------------------*/

/**
 * HELPER COMMENT START
 *
 * This file contains all of the frontend related javascript.
 * With frontend, it is meant the WordPress site that is visible for every visitor.
 *
 * You can add the localized variables in here as followed: dayschedul.plugin_name
 * These variables are defined within the localization function in the following file:
 * core/includes/classes/class-dayschedule-run.php
 *
 * HELPER COMMENT END
 *
 *
 * Button examples
 * <button dayschedule-url="https://demo.dayschedule.com" type="button">Book appointment</button>
 * <a dayschedule-url="https://demo.dayschedule.com" href="#">Book appointment</a>
 * <input dayschedule-url="https://demo.dayschedule.com" type="button">Book appointment</input>
 */

window.onload = (event) => {
  const buttons = document.querySelectorAll(
    "[dayschedule-url], [data-dayschedule-url]"
  );
  buttons.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      const url = e.target.getAttribute("dayschedule-url");
      daySchedule.initPopupWidget({
        url: url,
      });
    });
  });
};
