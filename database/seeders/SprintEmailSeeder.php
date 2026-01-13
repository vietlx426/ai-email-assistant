<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SprintEmailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('sprint_email_history')->delete();
        DB::table('email_pattern_vectors')->delete();
        DB::table('email_templates')->delete();
        DB::table('email_training_data')->delete();
        DB::table('user_sprint_preferences')->delete();

        DB::table('user_sprint_preferences')->insert([
            'user_identifier' => 'default',
            'default_sprint_data' => json_encode([
                'team_name' => 'Development Team',
                'manager_name' => 'Sarah Johnson',
                'sprint_length' => '2 weeks',
                'company_name' => 'TechCorp'
            ]),
            'writing_preferences' => json_encode([
                'tone' => 'professional',
                'formality' => 'medium',
                'length' => 'concise'
            ]),
            'confidence_threshold' => 0.70,
            'auto_learn' => true,
            'email_signatures' => json_encode([
                'default' => "Best regards,\n[Your Name]",
                'team' => "Thanks,\n[Your Name]\n[Team Name]"
            ]),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Seed training emails
        $trainingEmails = [
            [
                'email_type' => 'sprint_commitment',
                'subject_line' => 'December Sprint 3 Commitment and Goal',
                'content' => "Hi all,\nI hope you're doing well.\nFollowing our Sprint Planning session, here's a summary of our **December Sprint 3 Commitment and Goal**:\n\n**Sprint Goal**\nOur goal for this sprint is to focus on fixing VLE weekly defects and Behat failures. Our tester will ensure a stable release quality by verifying UI fixes for the Labs theme, validating accessibility improvements in the booking system, and testing bug fixes for the Study app v4.4.1.6 / v5.0.\n\n**Sprint Commitment**\n* **Commitment (Development + Testing)**\nID | Title | State | Release | Work Item Type | Tags\n926008 | StudyApp v4.4.1.6/5: Box styling issue | Developing | 2025-09b | Defect | Local testing; VLE weekly\n926808 | StudyApp 4.4.1.6/5: Dark mode visibility in assessment tab | Developing | 2025-09b | Defect | Local peer-code review; VLE weekly\n920726 | Labs: Cancel button in booking page is not aligned correctly | Committed | 2025-09b | Defect | VLE weekly\n\n* **Commitment (Development Only)**\nID | Title | State | Release | Work Item Type | Tags\n887566 | Behat:mod/forumng(tt,OUVLE_465 & OUVLE_467)-Verify \"Split\" | Committed | 2025-12a | Defect | CI Defect\n925494 | Behat: local/ouauthoring(tt,OUVLE_467) -Reverts and publishes the document. | Committed | 2025-12a | Defect | CI\n\nIf you believe any additional tickets would be a good fit, please feel free to assign them to us during the sprint, and we'll incorporate them into our plan accordingly.\n\nIf you have any questions or concerns, feel free to reach out.",
                'content_hash' => md5('real_sprint_commitment_december_sprint_3'),
                'is_processed' => false,
                'is_approved' => true,
                'metadata' => json_encode([
                    'real_example' => true,
                    'has_markdown' => true,
                    'has_table_structure' => true,
                    'commitment_categories' => 2
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'email_type' => 'sprint_commitment',
                'subject_line' => 'Sprint 23 Commitments - Development Team',
                'content' => "Hi Sarah,\n\nHere are our commitments for Sprint 23 (March 4-15):\n\n• Complete user authentication module\n• Fix critical bugs in payment system\n• Implement new dashboard design\n• Code review for mobile app features\n\nWe're confident about delivering these items based on our current capacity and previous sprint velocity.\n\nLet me know if you have any questions!\n\nBest,\nAlex",
                'content_hash' => md5('sprint_commitment_example_1'),
                'is_processed' => false,
                'is_approved' => true,
                'metadata' => json_encode(['example' => true]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'email_type' => 'sprint_update',
                'subject_line' => 'Sprint 23 Mid-Sprint Update',
                'content' => "Hi Sarah,\n\nQuick update on Sprint 23 progress:\n\n✅ Completed:\n• User authentication module (100%)\n• Payment system bug fixes (3/4 completed)\n\n🔄 In Progress:\n• Dashboard design implementation (60%)\n• Mobile app code reviews (ongoing)\n\n⚠️ Blockers:\n• Waiting for API documentation from backend team\n\nOverall we're tracking well to meet our commitments. The API blocker might delay mobile reviews by 1 day but shouldn't impact sprint completion.\n\nThanks,\nAlex",
                'content_hash' => md5('sprint_update_example_1'),
                'is_processed' => false,
                'is_approved' => true,
                'metadata' => json_encode(['example' => true]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'email_type' => 'retrospective',
                'subject_line' => 'Sprint 22 Retrospective Summary',
                'content' => "Team,\n\nHere's our Sprint 22 retrospective summary:\n\n🎯 What Went Well:\n• Great collaboration on the new feature\n• Improved our testing process\n• Met all sprint commitments\n\n🔧 What Could Improve:\n• Earlier identification of dependencies\n• Better time estimation for complex tasks\n• More frequent communication with stakeholders\n\n📋 Action Items:\n• Implement dependency mapping in sprint planning\n• Use story points more consistently\n• Schedule weekly stakeholder check-ins\n\nGreat job everyone on a successful sprint!\n\nBest,\nAlex",
                'content_hash' => md5('retrospective_example_1'),
                'is_processed' => false,
                'is_approved' => true,
                'metadata' => json_encode(['example' => true]),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('email_training_data')->insert($trainingEmails);

        // Seed basic email templates
        $templates = [
            [
                'pattern_type' => 'sprint_commitment',
                'template_name' => 'Standard Sprint Commitment',
                'template_content' => "Hi {{manager_name}},\n\nHere are our commitments for {{sprint_name}} ({{sprint_dates}}):\n\n{{commitments_list}}\n\nWe're confident about delivering these items based on our current capacity and previous sprint velocity.\n\nLet me know if you have any questions!\n\nBest,\n{{sender_name}}",
                'variables' => json_encode(['manager_name', 'sprint_name', 'sprint_dates', 'commitments_list', 'sender_name']),
                'style_attributes' => json_encode(['tone' => 'professional', 'structure' => 'bullet_list', 'confidence_level' => 'high']),
                'usage_count' => 0,
                'success_rate' => 0.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'pattern_type' => 'sprint_update',
                'template_name' => 'Mid-Sprint Progress Update',
                'template_content' => "Hi {{manager_name}},\n\nQuick update on {{sprint_name}} progress:\n\n✅ Completed:\n{{completed_items}}\n\n🔄 In Progress:\n{{in_progress_items}}\n\n{{blockers_section}}Overall we're {{progress_status}} to meet our commitments.{{additional_notes}}\n\nThanks,\n{{sender_name}}",
                'variables' => json_encode(['manager_name', 'sprint_name', 'completed_items', 'in_progress_items', 'blockers_section', 'progress_status', 'additional_notes', 'sender_name']),
                'style_attributes' => json_encode(['tone' => 'informative', 'structure' => 'status_sections', 'update_frequency' => 'mid-sprint']),
                'usage_count' => 0,
                'success_rate' => 0.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('email_templates')->insert($templates);

        $this->command->info('🎉 Sprint Email Learning System seeded successfully!');
        $this->command->line('📊 Seeded data:');
        $this->command->line('  - 1 user preference profile');
        $this->command->line('  - 4 example training emails (including your real one!)');
        $this->command->line('  - 2 basic email templates');
        $this->command->line('');
        $this->command->info('✅ Ready for Phase 1 implementation!');
    }
}