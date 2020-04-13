<?php

/**
 *  ===========================================================================
 *  COVID-19 CHALLENGE TEST
 *  ===========================================================================
 *  Your estimator will receive input data structured as JSON
 *
 *  {
 *    "region": {
 *      "name": "Africa",
 *      "avgAge": 19.7,
 *      "avgDailyIncomeInUSD": 5,
 *      "avgDailyIncomePopulation": 0.71
 *    },
 *    "periodType": "days",
 *    "timeToElapse": 58,
 *    "reportedCases": 674,
 *    "population": 66622705,
 *    "totalHospitalBeds": 1380614
 *  }
 *
 *
 * and the out put will be required to be an impact estimatation
 * having the data structures pecified as
 *
 *   {
 *      data: {},          // the input data you got
 *      impact: {},        // your best case estimation
 *      severeImpact: {}   // your severe case estimation
 *   }
 *
 *
 *  ==========================================================================
 *  CHALLENGE ONE (1)
 *  ===========================================================================
 *
 * (i) Currently Infected Patients Best Case
 * (ii) Convert Months, Weeks & Days to Basic Days
 * (iii) Estimated Infections in 28 Days. No rounding off the result
 */

// Currently Infected Patients Best Case
function currentlyInfected($data)
{
    // reportedCases
    $reported_cases = $data['reportedCases'];
    // currently Infected(* ten)
    $currently_infected = $reported_cases * 10;

    // echo $reported_cases;
    return $currently_infected;
}

// Currently Infected Patients Severe Case
function severeImpact($data)
{
    // Projected Cases
    $severe_impact = $data['reportedCases'];
    // Currently Infected(* ten)
    $severe_impact = $severe_impact * 50;

    // Echo $severe_impact;
    return $severe_impact;
};

/**
 * Convert Months, Weeks & Days to Basic Days
 * Years to Months and Months to Days
 * Months to Days
 */
function convertToDays($data)
{
    // Get the Duration Type
    $period_type    = $data['periodType'];
    $time_to_elapse = $data['timeToElapse'];

    switch ($period_type) {
        // If Months, convert to days
        case 'months':
            $duration = $time_to_elapse * 30;
            break;
        // if Weeks, convert to days
        case 'weeks':
            $duration = $time_to_elapse * 7;
            break;
        // if Days, leave as is
        default:
            $duration = $time_to_elapse;
            break;
    }

    return $duration;
};

// Estimated Infections. No rounding off the result
function infectionsByRequestedTime($data)
{
    // Normalize duration to days
    $duration = convertToDays($data);

    // Exponential Growth
    $factor = (int) ($duration / 3);
    $pow    = 2 ** $factor;

    // Currently Infected Patients - Impact
    $impact = currentlyInfected($data) * $pow;

    // Currently Infected Patients - Severe Cases
    $severe_impact = severeImpact($data) * $pow;

    // Affected Projection in 28 Days for both impact and Severe
    return array(
        'impact'        => $impact,
        'severe_impact' => $severe_impact,
    );
}

/**
 * ===========================================================================
 * CHALLENGE TWO (2)
 * ===========================================================================
 *
 * (i) Determine 15% of infections By Requested Time
 * (ii) Determine the number of available beds 35% basing on severeCasesByRequestedTime()
 */

// severe positive cases that will require hospitalization to recover
function severeCasesByRequestedTime($data)
{
    // Determine 15% of infections By Requested Time
    $impact        = (infectionsByRequestedTime($data)['impact']) * 0.15;
    $severe_impact = (infectionsByRequestedTime($data)['severe_impact']) * 0.15;

    // Return Severe Cases
    return array(
        'impact'        => $impact,
        'severe_impact' => $severe_impact,
    );
}

// Determine the number of available beds 35% basing on severeCasesByRequestedTime()
function hospitalBedsByRequestedTime($data)
{
    // 35% Hospital Beds Available
    $total_hospital_beds     = $data['totalHospitalBeds'];
    $available_hospital_beds = $total_hospital_beds * 0.35;

    // Bed Shottage
    $impact        = $available_hospital_beds - severeCasesByRequestedTime($data)['impact'];
    $severe_impact = $available_hospital_beds - severeCasesByRequestedTime($data)['severe_impact'];

    // Return Available Beds or Shotage
    return array(
        'impact'        => $impact,
        'severe_impact' => $severe_impact,
    );
}

/**
 * ===========================================================================
 * CHALLENGE THREE (3)
 * ===========================================================================
 *
 * (i) estimate number of severe positive cases that will require ICU care
 * (ii) cases For Ventilators By Requested Time
 */

// estimated number of severe positive cases that will require ICU care
function casesForICUByRequestedTime($data)
{
    // Determine 5% of infectionsByRequestedTime
    $impact        = infectionsByRequestedTime($data)['impact'] * 0.05;
    $severe_impact = infectionsByRequestedTime($data)['severe_impact'] * 0.05;

    // Return Cases that Require ICU
    return array(
        'impact'        => $impact,
        'severe_impact' => $severe_impact,
    );
}

// cases For Ventilators By Requested Time
function casesForVentilatorsByRequestedTime($data)
{
    // determine 2% of infections By Requested Time
    $impact        = infectionsByRequestedTime($data)['impact'] * 0.02;
    $severe_impact = infectionsByRequestedTime($data)['severe_impact'] * 0.02;

    // Return Cases that Require Ventilators
    return array(
        'impact'        => $impact,
        'severe_impact' => $severe_impact,
    );
}

// Estimate how much money the economy is likely to lose over the said period
/**
 * Finally, given the estimated number of infected people by the requested time and the AVG daily income
 * of the region, estimate how much money the economy is likely to lose over the said period. Save this as
 * dollarsInFlight in your output data structure. If 65% of the region (the majority) earn $1.5 a day, you
 * can compute dollarsInFlight over a 30 day period as
 *
 * // the final expressed with 2 decimal places
 * infectionsByRequestedTime x 0.65 x 1.5 x 30;
 *
 * dollars In Flight should have 2 decimal places
 */
function dollarsInFlight($data)
{

    // AVG daily income of the region
    $avg_daily_income_in_usd = $data['region']['avgDailyIncomeInUSD'];
    // requested time
    $duration = convertToDays($data);
    // Percentage of the region working
    $avg_daily_income_population = $data['region']['avgDailyIncomePopulation'];

    // infections by requested time
    $impact = infectionsByRequestedTime($data)['impact'];
    $severe_impact = infectionsByRequestedTime($data)['severe_impact'];

    $impact = ($impact * $avg_daily_income_population * $avg_daily_income_in_usd) / $duration;
    $severe_impact = ($severe_impact * $avg_daily_income_population * $avg_daily_income_in_usd) / $duration;

    // Return Dollar in flights lost
    return array(
        'impact'        => $impact,
        'severe_impact' => $severe_impact
    );
}

/**
 * Main Covid19 Impact Estimator Method
 */
function covid19ImpactEstimator($data)
{
    // convert input JSON string to Array
    $data = json_encode($data);
    $data = json_decode($data, true);

    // Challenge 1
    $currently_infected           = currentlyInfected($data);
    $severe_impact                = severeImpact($data);
    $infections_by_requested_time = infectionsByRequestedTime($data);

    // Challenge 2
    $severe_cases_by_requested_time  = severeCasesByRequestedTime($data);
    $hospital_beds_by_requested_time = hospitalBedsByRequestedTime($data);

    // Challenge 3
    $cases_for_icu_by_requested_time         = casesForICUByRequestedTime($data);
    $cases_for_ventilators_by_requested_time = casesForVentilatorsByRequestedTime($data);
    $dollars_in_flight                       = dollarsInFlight($data);

    // Output Data Structure
    $data = [
        'data'         => $data,
        'impact'       => [
            'currentlyInfected'                  => (int) $currently_infected,
            'infectionsByRequestedTime'          => (int) $infections_by_requested_time['impact'],
            'severeCasesByRequestedTime'         => (int) $severe_cases_by_requested_time['impact'],
            'hospitalBedsByRequestedTime'        => (int) $hospital_beds_by_requested_time['impact'],
            'casesForICUByRequestedTime'         => (int) $cases_for_icu_by_requested_time['impact'],
            'casesForVentilatorsByRequestedTime' => (int) $cases_for_ventilators_by_requested_time['impact'],
            'dollarsInFlight'                    => (int) $dollars_in_flight['impact']
        ],
        'severeImpact' => [
            'currentlyInfected'                  => (int) $severe_impact,
            'infectionsByRequestedTime'          => (int) $infections_by_requested_time['severe_impact'],
            'severeCasesByRequestedTime'         => (int) $severe_cases_by_requested_time['severe_impact'],
            'hospitalBedsByRequestedTime'        => (int) $hospital_beds_by_requested_time['severe_impact'],
            'casesForICUByRequestedTime'         => (int) $cases_for_icu_by_requested_time['severe_impact'],
            'casesForVentilatorsByRequestedTime' => (int) $cases_for_ventilators_by_requested_time['severe_impact'],
            'dollarsInFlight'                    => (int) $dollars_in_flight['severe_impact']
        ],
    ];

    // return the array
    return $data;
    // var_dump($data);
}

// Execute the Method
covid19ImpactEstimator($data);
// covid19ImpactEstimator(file_get_contents('./data.json', true));