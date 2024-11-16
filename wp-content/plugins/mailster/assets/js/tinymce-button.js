function validateForm() {
	const dobInput = document.getElementById('dob').value;
	const state = document.getElementById('state').value;
	const postcode = document.getElementById('postcode').value;
	const firstName = document.getElementById('firstName').value;
	const lastName = document.getElementById('lastName').value;
	const otherSkillsCheckbox = document.getElementById('otherSkills');
	const otherSkillsText = document.getElementById('otherSkillsText').value;
	const errorMessages = document.getElementById('errorMessages');
	errorMessages.textContent = ''; // Clear previous error messages

	// Date of Birth Validation
	const dobRegex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
	if (!dobRegex.test(dobInput)) {
		errorMessages.textContent += 'Invalid date format (dd/mm/yyyy).\n';
		event.preventDefault(); // Prevent form submission
	} else {
		const [, day, month, year] = dobRegex.exec(dobInput);
		const dobDate = new Date(`${year}-${month}-${day}`);
		const currentDate = new Date();
		const age = currentDate.getFullYear() - dobDate.getFullYear();
		if (age < 15 || age > 80) {
			errorMessages.textContent += 'Age must be between 15 and 80.\n';
			event.preventDefault();
		}
	}

	// State and Postcode Validation

	const stateToPostcode = {
		VIC: ['3', '8'],
		NSW: ['1', '2'],
		QLD: ['4', '9'],
		NT: ['0'],
		WA: ['6'],
		SA: ['5'],
		TAS: ['7'],
		ACT: ['0'],
	};
	if (!stateToPostcode[state] || !stateToPostcode[state].test(postcode)) {
		errorMessages.textContent += 'Postcode does not match selected state.\n';
		event.preventDefault();
	}

	// Other Skills Text Area Validation
	if (otherSkillsCheckbox.checked && otherSkillsText.trim() === '') {
		errorMessages.textContent += 'Please provide other skills.\n';
		event.preventDefault();
	}
	return true;
}
