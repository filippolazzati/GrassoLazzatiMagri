open util/integer
open util/ordering [Date] // adds ordering to such object

sig Farmer{
farm: one Farm,
productions: some ProductionData
}
sig Agronomist{
area: one Area
}
sig PolicyMaker{
}
sig Farm{
area: one Area
}
sig FarmVisit{
agronomist: one Agronomist,
farm: one Farm,
date: one Date
}
sig Area{
location: one String,
dimension: Int
}
sig ProductionData{
initialdate: one Date,
finaldate: one Date,
volume: one Int
}
sig Date{
}

fact{all a:Area | some agr:Agronomist | agr.area=a}
pred isRankedHigher(f1:Farmer, f2:Farmer){
f1.
}

fun numberofvisits(f:Farmer): one Int{
#(all fv:FarmVisit | fv.farm = f.farm)
}

assert ReferenceAgronomistArea{
all f:Farmer | some agr:Agronomist | agr.area = f.farm.area
} 
check ReferenceAgronomistArea for 5

assert rankvisits{
all f1:Farmer | all f2:Farmer | isRankedHigher(f1,f2) implies numberofvisits(f1) <= numberofvisits(f2)
}
check rankvisits for 5
run show{}
