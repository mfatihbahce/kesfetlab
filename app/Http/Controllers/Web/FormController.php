<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Workshop;
use App\Models\ParentUser;
use App\Models\ParentStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FormController extends Controller
{
    /**
     * Online form ana sayfası
     */
    public function index()
    {
        $workshops = Workshop::active()->get();
        
        return view('form.index', compact('workshops'));
    }

    /**
     * Form gönderimi
     */
    public function submit(Request $request)
    {
        $request->merge([
            'parent_phone' => $this->normalizePhoneForTr($request->input('parent_phone')),
            'emergency_contact_phone' => $this->normalizePhoneForTr($request->input('emergency_contact_phone')),
        ]);

        // Validation rules
        $rules = Student::rules();
        $rules['workshop_ids'] = 'required|array|min:1';
        $rules['workshop_ids.*'] = 'exists:workshops,id';

        $validator = Validator::make(
            $request->all(),
            $rules,
            [
                'school_name.required' => 'Şuan okuduğu okul adı alanı zorunludur.',
                'parent_phone.required' => 'Veli telefon numarası zorunludur.',
                'parent_phone.regex' => 'Veli telefon numarası 0 ile başlamalı ve 11 hane olmalıdır.',
                'emergency_contact_phone.required' => 'Acil durum telefon numarası zorunludur.',
                'emergency_contact_phone.regex' => 'Acil durum telefon numarası 0 ile başlamalı ve 11 hane olmalıdır.',
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Öğrenci verilerini hazırla (workshop_ids hariç)
            $studentData = $request->except(['workshop_ids']);
            
            // Öğrenci kaydını oluştur
            $student = Student::create($studentData);

            // Birden fazla sınıf secimi icin enrollment kayitlari olustur
            $workshops = Workshop::whereIn('id', $request->workshop_ids)->get();

            foreach ($workshops as $workshop) {
                $student->enrollments()->create([
                    'workshop_id' => $workshop->id,
                    'group_id' => null, // Henuz grup atanmamis
                    'status' => 'pending',
                    'enrollment_date' => now(),
                    'start_date' => now(),
                    'amount' => 0,
                    'payment_status' => 'pending',
                    'is_active' => true,
                ]);
            }

            return redirect()->route('form.success')
                ->with('success', 'Öğrenci kaydınız başarıyla alınmıştır. En kısa sürede size dönüş yapılacaktır.');

        } catch (\Exception $e) {
            \Log::error('Form submission error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Kayıt oluşturulurken bir hata oluştu. Lütfen tekrar deneyiniz.')
                ->withInput();
        }
    }

    /**
     * Başarı sayfası
     */
    public function success()
    {
        return view('form.success');
    }

    private function normalizePhoneForTr($phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if (strlen($digits) === 10 && !str_starts_with($digits, '0')) {
            return '0' . $digits;
        }

        return $digits;
    }
}
