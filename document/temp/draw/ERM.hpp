@startuml edudex_quiz_erd

' Entities
entity accounts {
  * id
  --
  * username
  * email
  * password
  is_active
  remember_token
  * role
  created_at
  updated_at
}

entity account_infos {
  * id
  --
  * account_id
  * fullName
  birthday
  avatar
  * gender
  * phoneNumber
  * address
  created_at
  updated_at
}

entity questions {
  * id
  --
  * content
  link_media
  * subject_code
  * difficulty
  created_at
  updated_at
}

entity answers {
  * id
  --
  * question_id
  * content
  link_media
  * is_correct
  created_at
  updated_at
}

entity subjects {
  * id
  --
  * code
  * name
  * credits
  * major_id
  description
  created_at
  updated_at
}

entity tags {
  * id
  --
  * name
  * subject_code
  created_at
  updated_at
}

entity question_tag {
  * question_id
  * tag_id
}

entity exams {
  * id
  --
  * name
  description
  * duration
  * total_questions
  * subject_code
  easy_rate
  medium_rate
  hard_rate
  created_at
  updated_at
}

entity exam_tags {
  * id
  --
  * exam_id
  * tag_id
  * num_questions
  easy_rate
  medium_rate
  hard_rate
  created_at
  updated_at
}

entity exam_periods {
  * id
  --
  * name
  description
  * start_time
  * end_time
  is_active
  created_at
  updated_at
}

entity exam_shifts {
  * id
  --
  * exam_period_id
  * name
  description
  * start_time
  * end_time
  is_active
  created_at
  updated_at
}

entity exam_period_subjects {
  * id
  --
  * exam_period_id
  * subject_id
  exam_id
  created_at
  updated_at
}

entity exam_period_subject_shifts {
  * id
  --
  * exam_period_subject_id
  * exam_shift_id
  created_at
  updated_at
}

entity exam_period_subject_students {
  * id
  --
  * exam_period_id
  * exam_period_subject_id
  * exam_code
  * student_code
  * full_name
  phone
  address
  birthday
  * gender
  created_at
  updated_at
}

entity facilities {
  * id
  --
  * code
  * name
  * address
  description
  is_active
  created_at
  updated_at
}

entity rooms {
  * id
  --
  * code
  * name
  * facility_id
  * capacity
  description
  is_active
  created_at
  updated_at
}

entity exam_period_rooms {
  * id
  --
  * exam_period_id
  * room_id
  created_at
  updated_at
}

entity exam_period_proctors {
  * id
  --
  * exam_period_id
  * account_id
  created_at
  updated_at
}

entity exam_shift_rooms {
  * id
  --
  * exam_shift_id
  * exam_period_room_id
  exam_period_subject_id
  exam_period_proctor_id
  created_at
  updated_at
}

entity exam_period_room_students {
  * id
  --
  * exam_period_id
  * exam_period_subject_id
  * exam_period_subject_student_id
  * exam_period_room_id
  * exam_shift_id
  * seat_number
  created_at
  updated_at
}

entity exam_results {
  * id
  --
  * exam_period_id
  * exam_shift_id
  * exam_period_subject_id
  * exam_id
  * exam_period_room_id
  * exam_period_proctor_id
  * exam_period_subject_student_id
  * exam_period_code
  * exam_shift_code
  * exam_subject_code
  * exam_code
  * room_code
  * proctor_code
  * student_code
  * correct_answers
  correct_answers_after_review
  * total_questions
  * score
  score_after_review
  note
  review_note
  reviewed_at
  reviewed_by
  * log_file
  created_at
  updated_at
}

entity faculties {
  * id
  --
  * code
  * name
  description
  created_at
  updated_at
}

entity majors {
  * id
  --
  * code
  * name
  * faculty_id
  description
  created_at
  updated_at
}

' Relationships
questions "many" -- "many" question_tag
exams "many" -- "many"  exam_tags
accounts ||--o{ account_infos
accounts ||--o{ exam_period_proctors

questions ||--o{ answers
questions }o--|| subjects
questions }o--o{ tags : question_tag

subjects }o--|| majors
subjects ||--o{ tags
subjects ||--o{ exams
subjects ||--o{ questions

tags }o--o{ exams : exam_tags

exams }o--o{ tags : exam_tags
exams }o--o{ exam_period_subjects

exam_periods ||--o{ exam_shifts
exam_periods ||--o{ exam_period_subjects
exam_periods ||--o{ exam_period_rooms
exam_periods ||--o{ exam_period_proctors
exam_periods ||--o{ exam_period_subject_students
exam_periods ||--o{ exam_results

exam_shifts ||--o{ exam_shift_rooms
exam_shifts ||--o{ exam_period_subject_shifts
exam_shifts ||--o{ exam_results

exam_period_subjects ||--o{ exam_period_subject_shifts
exam_period_subjects ||--o{ exam_period_subject_students
exam_period_subjects ||--o{ exam_results

exam_period_rooms ||--o{ exam_shift_rooms
exam_period_rooms ||--o{ exam_period_room_students
exam_period_rooms ||--o{ exam_results

rooms ||--o{ exam_period_rooms
facilities ||--o{ rooms

exam_period_proctors ||--o{ exam_shift_rooms
exam_period_proctors ||--o{ exam_results

exam_period_subject_students ||--o{ exam_period_room_students
exam_period_subject_students ||--o{ exam_results

faculties ||--o{ majors

@enduml