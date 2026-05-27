@extends('admin.master_admin')
@section('admin')

<div class="col-lg-12">
    <div class="card">
        <div class="card-body">
            <h4 class="mb-4">تعديل الترشيح</h4>

            <form action="{{ route('update.nomination', $nomination->id) }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">المستخدم</label>
                    <select name="user_id" class="form-control" required>
                        <option value="">اختر المستخدم</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ $user->id == $nomination->user_id ? 'selected' : '' }}>{{ $user->fname }}</option>
                        @endforeach
                    </select>
                    @error('user_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">المطية</label>
                    <select name="camal_id" class="form-control" required>
                        <option value="">اختر المطية</option>
                        @foreach($camals as $camal)
                            <option value="{{ $camal->id }}" {{ $camal->id == $nomination->camal_id ? 'selected' : '' }}>{{ $camal->name }}</option>
                        @endforeach
                    </select>
                    @error('camal_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">المهرجان</label>
                    <select name="festival_id" class="form-control" required>
                        <option value="">اختر المهرجان</option>
                        @foreach($festivals as $festival)
                            <option value="{{ $festival->id }}" {{ $festival->id == $nomination->festival_id ? 'selected' : '' }}>{{ $festival->name }}</option>
                        @endforeach
                    </select>
                    @error('festival_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">الجولة</label>
                    <select name="round_id" class="form-control" required>
                        <option value="">اختر الجولة</option>
                        @foreach($rounds as $round)
                            <option value="{{ $round->id }}" {{ $round->id == $nomination->round_id ? 'selected' : '' }}>{{ $round->name }}</option>
                        @endforeach
                    </select>
                    @error('round_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">فائز؟</label>
                    <input type="checkbox" name="is_winner" value="1" {{ $nomination->is_winner ? 'checked' : '' }}>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">تحديث الترشيح</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
