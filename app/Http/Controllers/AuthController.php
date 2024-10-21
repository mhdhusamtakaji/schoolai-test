<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Traits\ValidationRulesTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Otp;
use Illuminate\Support\Facades\Mail;
use App\Mail\OTPMail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use ValidationRulesTrait;

    public function register_teacher(Request $request)
    {
        $validator = Validator::make($request->all(), $this->userValidationRules());
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $request['role'] = 'teacher';

        return $this->processRegistration($request);
    }

    public function register_student(Request $request)
    {
        $validator = Validator::make($request->all(), $this->userValidationRules());
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $request['role'] = 'student';

        return $this->processRegistration($request);
    }

    private function sendOtp($userId)
    {
        $otp = rand(100000, 999999);
        $expires_at = Carbon::now()->addMinutes(15);

        $user = User::findorfail($userId);
        $otpRecord = Otp::create([
            'user_id' => $userId,
            'otp' => $otp,
            'expires_at' => $expires_at
        ]);

        $token = Str::random(60); // Generate a unique token
        session(['otp_token' => $token]); // Store the token in a session

        $emailData = [
            'otp' => $otp,
            'expires_at' => $expires_at->format('Y-m-d H:i:s')
        ];

        Mail::to($user->email)->send(new OTPMail($emailData));
    }

    public function verifyOtp(Request $request)
    {
        $otp = $request->input('otp');
        $token = session('otp_token'); // Get the token from the session

        $otpRecord = Otp::where('otp', $otp)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if ($otpRecord) {
            // OTP is valid, Verify User and delete the OTP record from the database
            $otpRecord->delete();
            User::findOrFail($otpRecord->user_id)->update(['email_verified_at' => Carbon::now()]);
            return response()->json(
                [
                    'message' => 'OTP verified'
                ], 200);

        } else {
            return response()->json(
                [
                    'message' => 'OTP is Invalid Or Expired, Please Try Again'
                ], 410);
        }

        // Clear the token from the session
        session()->forget('otp_token');
    }

    private function processRegistration(Request $request)
    {
        $request->request->remove('password_confirmation');
        $user = User::create($request->all());
        $token = $user->createToken('token-generate')->plainTextToken;

        if ($user->role == 'student'){
            $this->sendOtp($user->id);
        }

        return response()->json([
            'token' => $token,
            'Type' => 'Bearer',
            'role' => $user->role,
        ]);
    }

    private function validationErrorResponse($validator)
    {
        return response()->json(['message' => $validator->errors()], 422);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), $this->loginValidationRules());

        if ($validator->fails()) {
            // validation failure
            return response()->json(['message' => 'Validation failed'], 422);
        }

        $user = User::where('username', $request['username'])
        ->orWhere('email', $request['username'])
        ->first();

        // Check if user's email has been verified
        if ($user->role == 'student'){
            if (!$user || !$user->hasVerifiedEmail()) {
                return response()->json(['message' => 'Please verify your email first'], 401);
            }
        }

        $credentials = ['password' => $request['password']];

        if (filter_var($request['username'], FILTER_VALIDATE_EMAIL)) {
            $credentials['email'] = $request['username'];
        } else {
            $credentials['username'] = $request['username'];
        }

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('token-generate')->plainTextToken;

            return response()->json([
                'token' => $token,
                'Type' => 'Bearer',
                'role' => $user->role
            ]);
        }

        return response()->json(['message' => 'Wrong credentials'], 401);
    }

    public function updateStudentProfile(Request $request)
    {
        $validator = Validator::make($request->all(), $this->studentUpdateValidationRules());
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
        $user = $request->user();

        // only students are allowed to update thier profile
        if ($user->role !== 'student') {
            return response()->json(['message' => 'Permission denied.'], 403);
        }

        $user->update($request->only(['name', 'country', 'phone_number', 'major']));
        return response()->json(['message' => 'Student profile updated successfully']);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logout successful']);
    }


}
