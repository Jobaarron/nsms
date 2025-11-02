<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('case_meetings', function (Blueprint $table) {
            $table->boolean('written_reflection')->default(false)->after('summary');
            $table->date('written_reflection_due')->nullable()->after('written_reflection');
            $table->boolean('mentorship_counseling')->default(false)->after('written_reflection_due');
            $table->string('mentor_name')->nullable()->after('mentorship_counseling');
            $table->boolean('parent_teacher_communication')->default(false)->after('mentor_name');
            $table->date('parent_teacher_date')->nullable()->after('parent_teacher_communication');
            $table->boolean('restorative_justice_activity')->default(false)->after('parent_teacher_date');
            $table->date('restorative_justice_date')->nullable()->after('restorative_justice_activity');
            $table->boolean('follow_up_meeting')->default(false)->after('restorative_justice_date');
            $table->date('follow_up_meeting_date')->nullable()->after('follow_up_meeting');
            $table->boolean('community_service')->default(false)->after('follow_up_meeting_date');
            $table->date('community_service_date')->nullable()->after('community_service');
            $table->string('community_service_area')->nullable()->after('community_service_date');
            $table->boolean('suspension')->default(false)->after('community_service_area');
            $table->boolean('suspension_3days')->default(false)->after('suspension');
            $table->boolean('suspension_5days')->default(false)->after('suspension_3days');
            $table->integer('suspension_other_days')->nullable()->after('suspension_5days');
            $table->date('suspension_start')->nullable()->after('suspension_other_days');
            $table->date('suspension_end')->nullable()->after('suspension_start');
            $table->date('suspension_return')->nullable()->after('suspension_end');
            $table->boolean('expulsion')->default(false)->after('suspension_return');
            $table->date('expulsion_date')->nullable()->after('expulsion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_meetings', function (Blueprint $table) {
            $table->dropColumn([
                'written_reflection',
                'written_reflection_due',
                'mentorship_counseling',
                'mentor_name',
                'parent_teacher_communication',
                'parent_teacher_date',
                'restorative_justice_activity',
                'restorative_justice_date',
                'follow_up_meeting',
                'follow_up_meeting_date',
                'community_service',
                'community_service_date',
                'community_service_area',
                'suspension',
                'suspension_3days',
                'suspension_5days',
                'suspension_other_days',
                'suspension_start',
                'suspension_end',
                'suspension_return',
                'expulsion',
                'expulsion_date',
            ]);
        });
    }
};
