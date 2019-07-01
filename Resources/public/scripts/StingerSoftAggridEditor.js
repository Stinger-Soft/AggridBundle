StingerSoftAggrid.Editor.DatePicker = function(){
};

// gets called once before the renderer is used
StingerSoftAggrid.Editor.DatePicker.prototype.init = function(params) {
	// create the cell
	this.eInput = document.createElement('input');

	if (typeof params.value !== "undefined" && params.value !== null) {
		this.eInput.value = moment(params.value.date).format(moment.localeData().longDateFormat('L'));
	}

	// https://jqueryui.com/datepicker/
	jQuery(this.eInput).datepicker({
		format: moment.localeData().longDateFormat('L').toLowerCase()
	});
};

// gets called once when grid ready to insert the element
StingerSoftAggrid.Editor.DatePicker.prototype.getGui = function() {
	return this.eInput;
};

// focus and select can be done after the gui is attached
StingerSoftAggrid.Editor.DatePicker.prototype.afterGuiAttached = function() {
	this.eInput.focus();
	this.eInput.select();
};

// returns the new value after editing
StingerSoftAggrid.Editor.DatePicker.prototype.getValue = function() {
	if(this.eInput.value === "") {
		return null;
	}
	return {date: moment(this.eInput.value, 'L').toDate()};
};

// any cleanup we need to be done here
StingerSoftAggrid.Editor.DatePicker.prototype.destroy = function() {
	// but this example is simple, no cleanup, we could
	// even leave this method out as it's optional
};

// if true, then this editor will appear in a popup
StingerSoftAggrid.Editor.DatePicker.prototype.isPopup = function() {
	// and we could leave this method out also, false is the default
	return false;
};

