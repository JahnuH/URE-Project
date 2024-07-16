<?php
function mapDepartmentToCategoryAndType($userDepartment) {
    // Mapping of department codes to category names
    $departmentToCategory = [
        'BCOM' => 'B.COM',
        'UGE' => 'B.Tech',
        'UG' => 'B.tech-Student',
        'BBA' => 'BBA',
        'BCA' => 'BCA',
        'UGC' => 'BCOM/BBA/BAV',
        'CORE' => 'CORE',
        'DES' => 'Design',
        'DS' => 'Design Students',
        'FC' => 'Faculty',
        'LLM' => 'Law PG',
        'LLB' => 'LAW UG',
        'L' => 'Library',
        'PGE' => 'M.Tech',
        'MCA' => 'Master of Computer Applications',
        'PGLAW' => 'Master of Laws degree',
        'PGM' => 'MBA',
        'BAJ' => 'Media Studies',
        'ST' => 'PG Student',
        'MTECH' => 'PG-M.Tech',
        'RES' => 'Research Scholar',
        'RS' => 'Research Scholar',
        'S' => 'Staff',
        'STF' => 'Staff',
        'STU' => 'Student',
        'UGLLB' => 'UG_LAW'
    ];

    // Mapping of category names to types
    $categoryToType = [
        'B.COM' => 'student',
        'B.Tech' => 'student',
        'B.tech-Student' => 'student',
        'BBA' => 'student',
        'BCA' => 'student',
        'BCOM/BBA/BAV' => 'student',
        'CORE' => 'student',
        'Design' => 'student',
        'Design Students' => 'student',
        'Faculty' => 'faculty',
        'Law PG' => 'student',
        'LAW UG' => 'student',
        'Library' => 'student',
        'M.Tech' => 'student',
        'Master of Computer Applications' => 'student',
        'Master of Laws degree' => 'student',
        'MBA' => 'student',
        'Media Studies' => 'student',
        'PG Student' => 'student',
        'PG-M.Tech' => 'student',
        'Research Scholar' => 'Research Scholar',
        'Staff' => 'student',
        'Student' => 'student',
        'UG_LAW' => 'student'
    ];

    // Map the user department to the category name
    if (array_key_exists($userDepartment, $departmentToCategory)) {
        $userDepartment = $departmentToCategory[$userDepartment];
    } else {
        // Handle the case where the department code is not found
        throw new Exception("Unknown department code: " . $userDepartment);
    }

    // Map the category name to the type
    if (array_key_exists($userDepartment, $categoryToType)) {
        $userCategory = $categoryToType[$userDepartment];
    } else {
        // Handle the case where the category name is not found
        throw new Exception("Unknown category name: " . $userDepartment);
    }

    return [$userDepartment, $userCategory];
}
?>
