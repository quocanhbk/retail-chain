import 'package:bkrm/services/info/inventoryInfo/priceHistory.dart';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

class PriceHistoryPage extends StatefulWidget {
  final PriceHistory priceHistory;

  PriceHistoryPage(this.priceHistory);

  @override
  _PriceHistoryPageState createState() => _PriceHistoryPageState();
}

class _PriceHistoryPageState extends State<PriceHistoryPage> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text("Lịch sử chỉnh sửa giá"),),
      body: Container(
        padding: EdgeInsets.all(8.0),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Row(
              mainAxisSize: MainAxisSize.max,
              children: [
                Expanded(flex:1,child: Text("Từ",style: TextStyle(fontSize: 16,fontWeight: FontWeight.bold),)),
                Expanded(flex:1,child: Text("Đến",style: TextStyle(fontSize: 16,fontWeight: FontWeight.bold),)),
                Expanded(flex:1,child: Text("Giá",style: TextStyle(fontSize: 16,fontWeight: FontWeight.bold),)),
                Expanded(flex:1,child: Text("Điều chỉnh bởi",style: TextStyle(fontSize: 16,fontWeight: FontWeight.bold),)),
              ],
            ),
            Divider(),
            Expanded(
            child: widget.priceHistory.prices.isNotEmpty?ListView.separated(itemBuilder: (context,index){
              return Row(
                children: [
                  Expanded(flex:1,child: Text(DateFormat("dd-MM-yyyy").format(widget.priceHistory.prices[index].fromDate))),
                  Expanded(flex:1,child: Text(widget.priceHistory.prices[index].toDate!=null?DateFormat("dd-MM-yyyy").format(widget.priceHistory.prices[index].toDate!):"Hiện tại")),
                  Expanded(flex:1,child: Text(NumberFormat().format(widget.priceHistory.prices[index].sellPrice))),
                  Expanded(flex:1,child: Text(widget.priceHistory.prices[index].changedBy)),
                ],
              );
            }, separatorBuilder: (context,index)=>Divider(), itemCount: widget.priceHistory.prices.length):
            Container(
              child: Center(
                child: Text("Không có lần chỉnh sửa giá nào.",style: TextStyle(fontSize: 18,fontWeight: FontWeight.w300,color: Colors.grey),),
              ),
            ),
          ),]
        )
      ),
    );
  }
}
