<?php

use LaravelRU\User\Forms\LoginForm;
use LaravelRU\User\Forms\RegistrationForm;
use LaravelRU\User\Models\Confirmation;
use LaravelRU\User\Models\User;

class AuthController extends BaseController {

	/**
	 * @var RegistrationForm
	 */
	private $registrationForm;

	/**
	 * @var LoginForm
	 */
	private $loginForm;

	public function __construct(RegistrationForm $registrationForm, LoginForm $loginForm)
	{
		$this->registrationForm = $registrationForm;
		$this->loginForm = $loginForm;
	}

	public function getRegistration()
	{
		$jsToken = Str::quickRandom(10);
		Session::set('jsToken', $jsToken);

		return View::make('auth/registration', compact('jsToken'));
	}

	public function postRegistration()
	{
		$input = Input::only('name', 'email', 'password', 'js_token');

		$this->registrationForm->validate($input);

		unset($input['js_token']);

		$user = User::create($input);
		$user->save();

		$confirmationString = Str::quickRandom(20);

		$userConfirmation = new Confirmation();
		$userConfirmation->code = $confirmationString;

		$user->confirmation()->save($userConfirmation);

		$email = $input['email'];
		$password = $input['password'];
		Mail::queue('emails/auth/register', ['confirmationString' => $confirmationString], function ($message) use ($email)
		{
			$message->from('postmaster@sharedstation.net');
			$message->to($email);
			$message->subject('Подтверждение регистрации');
		});

		return Redirect::route('auth.registration.preconfirmation');
	}

	public function getPreconfirmation()
	{
		return View::make('auth/preconfirmation');
	}

	public function getConfirmation($code)
	{
		$userConfirmation = Confirmation::where('code', $code)->first();

		if ($userConfirmation)
		{
			$user = $userConfirmation->user;
			$user->is_confirmed = 1;
			$user->save();
			$userConfirmation->delete();

			Auth::login($user);

			return View::make('auth/confirmation_success');
		}

		return View::make('auth/confirmation_error');
	}

	public function getLogin()
	{
		return View::make('auth/login');
	}

	public function postLogin()
	{
		$credentials = Input::only('email', 'password');

		$this->loginForm->validate($credentials);

		$success = Auth::attempt($credentials, true, true);

		if ( ! $success)
		{
			return Redirect::route('auth.login')
				->withErrors(['wrong_input' => 'Неправильный email или пароль.'])
				->onlyInput('email');
		}

		return Redirect::intended();
	}

	public function getLogout()
	{
		Auth::logout();

		return Redirect::to('/');
	}

}
