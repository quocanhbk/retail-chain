import 'package:bkrm/services/info/inventoryInfo/itemInfo.dart';

class ChartInfo {
  DateTime fromFate;
  DateTime toDate;
  String unit;
  List<int> revenue;
  List<int> profit;
  List<int> capital;
  List<int> purchase;
  int maxValue = 0;
  int minValue =0;
  int totalRevenue=0;
  int totalCapital=0;
  int totalProfit=0;
  int totalPurchase=0;
  List<ItemInfo> noPurchasePriceItem = [];
  ChartInfo(this.fromFate, this.toDate, this.unit, this.revenue, this.profit,
      this.capital,this.purchase,this.noPurchasePriceItem){
    totalRevenue=getTotalRevenue();
    totalCapital=getTotalCapital();
    totalProfit=getTotalProfit();
    totalPurchase=getTotalPurchase();
    if(maxValue==0){
      maxValue=100000;
    }
  }

  int getTotalRevenue() {
    int total = 0;
    revenue.forEach((element) {
      if (element > maxValue) {
        maxValue = element;
      }
      if(element<minValue){
        minValue=element;
      }
      total += element;
    });
    return total;
  }

  int getTotalProfit() {
    int total = 0;
    profit.forEach((element) {
      if (element > maxValue) {
        maxValue = element;
      }
      if(element<minValue){
        minValue=element;
      }
      total += element;
    });
    return total;
  }

  int getTotalCapital() {
    int total = 0;
    capital.forEach((element) {
      if (element > maxValue) {
        maxValue = element;
      }
      if(element<minValue){
        minValue=element;
      }
      total += element;
    });
    return total;
  }

  int getTotalPurchase() {
    int total = 0;
    purchase.forEach((element) {
      if (element > maxValue) {
        maxValue = element;
      }
      if(element<minValue){
        minValue=element;
      }
      total += element;
    });
    return total;
  }
  @override
  String toString() {
    return revenue.toString()+" "+capital.toString()+" "+profit.toString()+" "+totalProfit.toString()+" "+totalCapital.toString()+" "+totalProfit.toString()+" "+maxValue.toString();
  }
}