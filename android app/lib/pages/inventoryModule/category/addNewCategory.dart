import 'package:bkrm/pages/Nav2App.dart';
import 'package:bkrm/services/services.dart';
import 'package:bkrm/widget/menuWidget.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';

class AddNewCategoryPage extends StatefulWidget {
  @override
  _AddNewCategoryPageState createState() => _AddNewCategoryPageState();
}

class _AddNewCategoryPageState extends State<AddNewCategoryPage> {
   TextEditingController categoryController = TextEditingController();
   bool categoryValid = false;
   bool needRefresh = false;
   GlobalKey<State>? _formKey ;
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text("Thêm danh mục mới"),
      ),
      body: WillPopScope(
        onWillPop: ()async{
          Navigator.pop(context,needRefresh);
          return needRefresh;
        },
        child: Container(
          padding: EdgeInsets.all(8.0),
          child: Form(
            key: _formKey,
            child: Column(
              children: [
                Center(
                  child: Text("Thêm danh mục",style: TextStyle(fontWeight: FontWeight.bold,fontSize: 24),),
                ),
                SizedBox(height: 30,),
                Row(children: [
                  Expanded(flex:1,child: Text("Tên danh mục: ",style: TextStyle(fontSize: 16,fontWeight: FontWeight.w400),)),
                  Expanded(
                    flex: 2,
                    child: TextFormField(
                      controller: categoryController,
                      autovalidateMode: AutovalidateMode.always,
                      validator: (category){
                        if(category==""||category==null){
                          categoryValid=false;
                          return " * Bắt buộc";
                        }else{
                          categoryValid=true;
                          return null;
                        }
                      },
                    ),
                  )
                ],),
                SizedBox(height: 30,),
                Center(
                  child:
                  RaisedButton(
                    color: Colors.blue,
                    padding: EdgeInsets.all(8.0),
                    onPressed: ()async{
                      if(categoryValid){
                        showDialog(context: context,builder: (context){
                          return AlertDialog(
                            content: Container(
                              height: 50,
                              child: Center(
                                child: CircularProgressIndicator(),
                              ),
                            ),
                          );
                        });
                        MsgInfoCode? returnStatus = await BkrmService().createCategory(name: categoryController.value.text);
                        if(returnStatus==MsgInfoCode.actionSuccess){
                          showDialog(context: context,builder: (context){
                            return AlertDialog(
                              content: Container(
                                height: 50,
                                child: Center(
                                  child: Text("Thêm danh mục thành công",style: TextStyle(fontSize: 20),),
                                ),
                              ),
                              actions: [
                                FlatButton(onPressed: (){
                                  needRefresh=true;
                                  Navigator.pop(context);
                                  Navigator.pop(context);
                                  Navigator.pop(context,needRefresh);
                                }, child: Text("Đóng",style: TextStyle(fontSize : 20),))
                              ],
                            );
                          });
                          return;
                        }else{
                          showDialog(context: context,builder: (context){
                            return AlertDialog(
                              content: Container(
                                height: 50,
                                child: Center(
                                  child: Text("Thêm danh mục thất bại",style: TextStyle(fontSize: 20),),
                                ),
                              ),
                              actions: [
                                FlatButton(onPressed: (){
                                  Navigator.pop(context);
                                }, child: Text("Đóng",style: TextStyle(fontSize: 20),))
                              ],
                            );
                          });
                        }
                      }
                    },
                    child: Text("Thêm",style: TextStyle(fontSize: 20,fontWeight: FontWeight.bold),),
                  ),
                )
              ],
            ),
          ),
        ),
      ),
    );
  }
}
