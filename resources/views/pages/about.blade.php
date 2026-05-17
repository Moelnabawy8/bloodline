@extends('layouts.app')

@section('title', 'About Us')

@section('content')
<div class="container-bl py-4" style="max-width:800px;">
    <h1 style="color:var(--main-red);font-weight:bold;text-align:center;margin-bottom:24px;">About BloodLine</h1>
    <div style="background:var(--card-bg);padding:30px;border-radius:14px;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
        <p style="font-size:17px;line-height:1.8;color:#444;">
            BloodLine is a community platform dedicated to connecting blood donors with patients in urgent need across Egypt.
            Our mission is simple: <strong>save lives</strong> by removing the friction between those who can give and those who need help.
        </p>
        <h3 style="color:var(--main-red);margin:24px 0 10px;">What we do</h3>
        <ul style="line-height:2;color:#555;">
            <li>Register and verify blood donors across all governorates.</li>
            <li>Allow hospitals to post urgent blood requests in real-time.</li>
            <li>Match donors to nearby requests based on blood type and location.</li>
            <li>Organize blood donation campaigns and awareness events.</li>
            <li>Reward regular donors with our point system and leaderboard.</li>
        </ul>
        <h3 style="color:var(--main-red);margin:24px 0 10px;">Our vision</h3>
        <p style="line-height:1.8;color:#444;">
            A future where no patient dies waiting for a blood donation. Where every region of Egypt has a thriving community of heroes ready to give.
        </p>
    </div>
</div>
@endsection
