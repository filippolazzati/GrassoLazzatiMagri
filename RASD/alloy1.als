open util/integer
open util/ordering [DateTime] // adds ordering to DateTime objects

/**** SIGNATURES ****/
sig DateTime {}
abstract sig User {}
sig Farmer extends User {
	farm: one Farm
}
sig Agronomist extends User {
	// For the purposes of this Alloy specification,
	// we assume all Agronomists have already selected their area.
	area: one Area
}
sig Farm {
	area: one Area,
}
sig DailyPlan {
	agronomist: one Agronomist,
	fromDateTime: one DateTime,
	toDateTime: one DateTime
}
sig FarmVisit {
	dailyPlan: one DailyPlan,
	farm: one Farm,
	dateTime: one DateTime
}
sig Area {}
sig ProductionData {
	farm: one Farm,
	fromDateTime: one DateTime,
	toDateTime: one DateTime,
	volume: one Int
}
sig ProductionIssue {
	productionData: one ProductionData
}

/**** FUNCTIONS ****/
fun FarmVisits[fx: Farm]: set FarmVisit {
	{ fv: FarmVisit | fv.farm = fx }
}

fun FarmerVisits[f: Farmer]: set FarmVisit {
	FarmVisits[f.farm]
}

fun FarmVisitAgronomist[fv: FarmVisit]: one Agronomist {
	fv.dailyPlan.agronomist
}

fun AgronomistVisits[a: Agronomist]: set FarmVisit {
	{ fv: FarmVisit | FarmVisitAgronomist[fv] = a }
}

fun FarmerIssues[f: Farmer]: set ProductionIssue {
	{ i: ProductionIssue | i.productionData.farm = f.farm }
}

fun AgronomistDailyPlans[a: Agronomist]: set DailyPlan {
	{ p: DailyPlan | p.agronomist = a }
}

/**** FACTS ****/
// Every farm has exactly one farmer
fact { all fx: Farm | one f: Farmer | f.farm = fx }

// All areas have at least one agronomist
fact { all a: Area | some agr: Agronomist | agr.area = a }

// All areas have at least one farmer
fact { all a: Area | some f: Farmer | f.farm.area = a }

// All farms are visited at least twice
// (Note: For the purposes of this specification, we assume all visits
// in the generated world happen in the same year)
fact { all fx: Farm | (let v = FarmVisits[fx] | #v >= 2) }

// Every Daily Plan has at least one visit
fact { all p: DailyPlan | some v: FarmVisit | v.dailyPlan = p }

// Daily Plan dates are consistent
fact { all p: DailyPlan | lt[p.fromDateTime, p.toDateTime] }

// Farm Visit dates are consistent with their daily plan
fact { all fv: FarmVisit | (gte[fv.dateTime, fv.dailyPlan.fromDateTime] and lte[fv.dateTime, fv.dailyPlan.toDateTime]) }

// Agronomists only visit farms in their area
fact { all fv: FarmVisit | fv.farm.area = FarmVisitAgronomist[fv].area }

// Farmers who had more production issues are visited more often
// (relative to other farmers in their area)
fact {
	all disj f1: Farmer, f2: Farmer |
		(f1.farm.area = f2.farm.area and #FarmerIssues[f1] > #FarmerIssues[f2])
		implies
		#FarmerVisits[f1] >= #FarmerVisits[f2]
}

// Agronomists in the same area "split" their work,
// i.e no agronomist does more than twice
// the number of visits of any of their colleagues.
fact { all disj a1: Agronomist, a2: Agronomist | a1.area = a2.area implies #AgronomistVisits[a1] <= mul[2, #AgronomistVisits[a2]] }

// Production volume is nonnegative
fact { all d: ProductionData | d.volume >= 0 }

// Production datetimes are consistent
fact { all d: ProductionData | lt[d.fromDateTime, d.toDateTime] }

/**** PREDICATES (WORLD GENERATION) ****/
pred WorldSimple {
	#Area = 1
	#Farmer = 1
	#Agronomist = 1
}

pred WorldManyAgronomists {
	#Area = 1
	#Farmer = 2
	#Agronomist = 5
	#FarmVisit = 5
}

pred WorldFarmersIssues {
	#Area = 1
	#Farmer = 2
	#Agronomist = 2
	#ProductionData = 2
	#ProductionIssue = 5
	#FarmVisit = 5
	// Every farmer had at least one issue
	all f: Farmer | #FarmerIssues[f] >= 1
}

/**** ASSERTIONS ****/
// All farmers have a "reference" agronomist
//(i.e. the agronomist they can contact via DREAM)
assert FarmerReferenceAgronomists {
	all f: Farmer | some agr: Agronomist | f.farm.area = agr.area
}

// All agronomists have at least one daily plan
assert AgronomistDailyPlans {
	all a: Agronomist | #AgronomistDailyPlans[a] > 0
}

// All agronomists visit some farms
assert AgronomistFarmVisits {
	all a: Agronomist | #AgronomistVisits[a] > 0
}

/**** EXECUTION ****/
check FarmerReferenceAgronomists for 5
check AgronomistFarmVisits for 5
check AgronomistDailyPlans for 5
run WorldSimple for 5
run WorldManyAgronomists for 10
run WorldFarmersIssues for 5
