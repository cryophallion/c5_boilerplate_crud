# Boilerplate code for basic dashboard CRUD operations in Concrete5

A Concrete5 package containing a basic dashboard interface that can serve as a skeleton for new projects. Contains a sample implementation of a two-table data model (with a parent-to-child relationship) and a dashboard UI for editing that data.

This code demonstrates one method of structuring files and interacting with the Concrete5 API (including controller routing and view rendering). It's definitely not the only way you could do things, but I happen to like it and have honed it down over the course of many real-world projects.

My aims were to have both a skeleton file/directory structure and to simplify and DRY up the code used for the standard CRUD pattern (one page to list all records, one to add/edit an a record, and one to delete a record).

## "Improvements" to Concrete5
I started off following the general pattern outlined in this most excellent how-to:
[How-To: Build a Single-Page Powered Editing Interface for concrete5 Pages](http://www.concrete5.org/documentation/how-tos/developers/build-a-single-page-powered-editing-interface-for-concrete5/), then added a few libraries to smooth out the rough edges -- routing/rendering/redirecting, basic database CRUD operations, and form/data validation:

 * `libraries/crud_controller.php`: overrides render(), redirect(), and url() methods of the base Controller class to make them easier to call (fewer and shorter arguments); provides simple "flash message" functionality for displaying a message after a redirect; provides automatic CSRF protection on all POST's.
 * `libraries/basic_crud_model.php`: a super basic data model class you can build your own models on top of -- provides automatic INSERT/UPDATE, DELETE, and 'SELECT ONE' functionality, and that's it! All it does is allow you easily perform single record inserts/updates/deletes/selects -- because I've found that everything else always requires custom queries anyway, so why bother abstracting it (there's nothing wrong with SQL, as long as you can keep it DRY).
 * `libraries/kohana_validation.php`: provides a *much* more robust set of validation rules than C5's built-in validation helpers. (Ported from the awesome Kohana 2.3.4 framework, hence the name.)

### Controller
In the controller (`controllers/dashboard/boilerplate_crud.php`), I tried to use a very streamlined and DRY approach. Most notably, I prefer to have a separate view file for each action, even when the action methods are in a single controller file (as opposed to having a single view file for all of a controller's actions, as demonstrated in the afore-linked "[How-To](http://www.concrete5.org/documentation/how-tos/developers/build-a-single-page-powered-editing-interface-for-concrete5/)").

The general workflow is: there are 4 pages for each data entity, corresponding to the 4 CRUD operations: list all records, add a new record, edit an existing record, delete a record. Each of these pages has a separate view file, but all action methods are in a single controller file (I actually combine the actions of two entities in the controller, but splitting it up into two controllers wouldn't be a terrible idea). The add and edit actions are combined into one method because they are basically the same thing (one just starts out with pre-populated data). The add/edit workflow can be distilled down to:
	
	//INSERT or UPDATE a record (empty/missing id means INSERT)
	public function something_edit($id = null) {
		//Check for form submissions
		if ($this->post()) {
			//Validate submitted data (using our own specific validation function (defined by us in the data model)
			$error = $this->data->validate($this->post());
			if ($error->has()) {
				//Redisplay form with error messages at top (C5 will automatically repopulate user's POST'ed data via the form helpers in the view)
				$this->set('error', $error);
			} else {
				//Validation success: save, redirect, and display message
				$this->data->save($this->post());
				$this->flash('Something Saved!');
				$this->redirect('something_list'); //assumes action in same controller
			}
		} else {
			//Pre-populate form fields: retrieve database record and send all its fields to the view
			$something = $this->data->findById($id);
			$this->setArray($something);
		}
		
		//Display the view
		$this->set('id', $id); //so view knows if it's an insert or update (and can pass it back to us in the submit)
		$this->render('something/edit'); //assumes view file in package's single_pages directory
	}

### Database CRUD
Each of the model files (`models/category.php` and `models/widget.php`) contains a class representing a single database table. They each extend the "basic_crud_model" class, which handles the INSERT, UPDATE, DELETE queries automatically (as well as SELECTing one record) via the `save()`, `delete()`, and `findById()` methods. All other database interaction (including validation) is handled by custom functions in the two model classes. I prefer this lightweight solution to a full-blown ORM because Concrete5 packages are usually relatively small in scope, so it's not overly complicated to just use SQL statements for the few tricky queries you'll need. The most important thing is that you separate your concerns by putting database access in an isolated place -- how you actually access the database from that isolated place doesn't really matter too much IMHO.

### CSRF Tokens
CSRF tokens will be automatically validated for every POST request. You must put `<?php echo $token; ?>` inside every form in your views (including delete confirmation forms), otherwise you'll get an "invalid token" error when submitting add/edit/delete forms.

### Validation
If you look at the validate() methods in the two model files (`models/category.php` and `models/widget.php`), you'll see examples of using the validation library. It's generally something like this:

	public function validate($post) {
		Loader::library('kohana_validation', 'boilerplate_crud');
		$v = new KohanaValidation($post);
		
		$v->add_rule('fieldname', 'validationrule', 'Validation failure error message');
		$v->add_rule('fieldname', 'validationrule', 'Validation failure error message');
		$v->add_rule('fieldname', 'validationrule', 'Validation failure error message');
		//etc...
		
		$v->validate();
		return $v->errors(true); //pass true to get a C5 "validation/error" object back
	}

See https://github.com/jordanlev/c5_kohana_form_validator for more details (including a link to the full list of available validation rules).
