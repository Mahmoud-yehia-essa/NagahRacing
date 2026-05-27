@extends('admin.master_admin')
@section('admin')

<div class="page-content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card">
                    <div class="card-body">
                        <h4 class="mb-4">تعديل نقاط كل المهرجنات المضافة</h4>

                        {{-- <form method="POST" action="{{ route('update.festival.points') }}"> --}}
                            <form method="POST" action="{{ route('update.festival.points', $point->id) }}">

                            @csrf
                            <input type="hidden" name="id" value="{{ $point->id }}">

                         <input type="hidden" name="festival_id" value="{{ $point->festival_id }}">


                            {{-- اختيار المهرجان --}}
                            {{-- <div class="mb-3">
                                <label class="form-label">المهرجان</label>
                                <select name="festival_id" class="form-control" required>
                                    <option value="">اختر المهرجان</option>
                                    @foreach($festivals as $festival)
                                        <option value="{{ $festival->id }}" {{ $festival->id == $point->festival_id ? 'selected' : '' }}>
                                            {{ $festival->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('festival_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div> --}}

                            {{-- اختيار الفئة العمرية --}}


                             <div class="mb-3">
                                <label class="form-label">الفئة العمرية</label>
                                <input readonly type="text" name="age_name" class="form-control" value="{{ $point->age_name }}" required>
                                @error('age_name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            {{-- <div class="mb-3">
                                <label class="form-label">الفئة العمرية</label>
                                <select  name="age_name" class="form-control" required>
                                    <option value="">اختر الفئة</option>
                                    @foreach(['الحقايق','اللقايا','الجذاع','الثنايا','زمول','الحيل'] as $age)
                                        <option value="{{ $age }}" {{ $age == $point->age_name ? 'selected' : '' }}>{{ $age }}</option>
                                    @endforeach
                                </select>
                                @error('age_name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div> --}}

                            {{-- إدخال النقاط --}}
                            <div class="mb-3">
                                <label class="form-label">النقاط</label>
                                <input type="number" name="points" class="form-control" value="{{ $point->points }}" required>
                                @error('points') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="text-end">
                                <input type="submit" class="btn btn-primary" value="تحديث النقاط">
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection
