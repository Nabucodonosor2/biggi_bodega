----------------------------- spu_item_deposito ------------------------
create PROCEDURE spu_item_deposito(@ve_operacion				varchar(20)
									,@ve_cod_item_deposito		numeric
									,@ve_cod_deposito			numeric=null
									,@ve_cod_doc_ingreso_pago	numeric=null
									,@ve_seleccion				varchar(1)=null)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		if (@ve_seleccion='S')
			insert into item_deposito
				(cod_deposito
				,cod_doc_ingreso_pago)
			values 
				(@ve_cod_deposito
				,@ve_cod_doc_ingreso_pago)
	end 
	else if (@ve_operacion='UPDATE') begin
		if (@ve_seleccion='N')
			delete item_deposito
			where cod_item_deposito = @ve_cod_item_deposito
	end
END