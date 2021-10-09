import 'package:bkrm/services/info/inventoryInfo/supplierInfo.dart';
import 'package:bkrm/services/services.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';

class ListSupplier extends StatefulWidget {
  late Function (BuildContext,SupplierInfo) onTapSupplier;
  @override
  State<StatefulWidget> createState() => _ListCustomersState();

  ListSupplier({required Function(BuildContext,SupplierInfo) onTapSupplier}){
    this.onTapSupplier=onTapSupplier;
  }
}

class _ListCustomersState extends State<ListSupplier> {
  BkrmService bkrmService = BkrmService();
  String searchQuery="";
  TextEditingController controller = TextEditingController();
  List<SupplierInfo>? suppliersData;
  @override
  void initState() {
    super.initState();
    getCustomerData();
  }
  void getCustomerData()async{
    suppliersData = (await bkrmService.getSupplier()).reversed.toList();
    setState(() {});
  }
  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: (){
        FocusScope.of(context).requestFocus(null);
      },
      child: AlertDialog(
        title: Text("Nhà cung cấp"),
        content: SingleChildScrollView(
          child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                TextField(
                  controller: controller,
                  style: TextStyle(fontSize: 12),
                  onChanged: (value) {
                    searchQuery=value.toLowerCase();
                    setState(() {});
                  },
                  decoration: InputDecoration(
                      isDense: true,
                      labelText: "Tìm kiếm",
                      hintText: "Nhập tên nhà cung cấp",
                      prefixIcon: Icon(Icons.search),
                      border: OutlineInputBorder(
                          borderRadius: BorderRadius.all(Radius.circular(25.0)))),
                ),Container(
                    child: suppliersData==null?
                    Container(
                      height: 250,
                      child: Center(child: CircularProgressIndicator()),
                    ):
                    Container(
                      height: 250,
                      width: MediaQuery.of(context).size.width*0.75,
                      child: ListView.builder(shrinkWrap: true,itemCount: suppliersData!.length,itemBuilder:(context,index){
                        if(searchQuery!=""){
                          if(suppliersData![index].name.toString().toLowerCase().contains(searchQuery)||suppliersData![index].phoneNumber.toString().toLowerCase().contains(searchQuery)){
                            return ListTile(
                              title: Text(suppliersData![index].name!),
                              subtitle: Text(suppliersData![index].phoneNumber!),
                              onTap: (){
                                widget.onTapSupplier(context,suppliersData![index]);
                              }
                            );
                          }else{
                            return Container();
                          }
                        }else{
                          return ListTile(
                            title: Text(suppliersData![index].name!),
                            subtitle: Text(suppliersData![index].phoneNumber!),
                            onTap: (){
                              widget.onTapSupplier(context,suppliersData![index]);
                            }
                          );
                        }
                      } ),
                    )
                ),
              ]
          ),
        ),
      ),
    );
  }
}