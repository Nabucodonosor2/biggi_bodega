-------------------- spu_doc_ingreso_pago ---------------------------------
CREATE PROCEDURE [dbo].[spu_doc_ingreso_pago](
				@ve_operacion				varchar(20)
				,@ve_cod_doc_ingreso_pago	numeric
				,@ve_cod_ingreso_pago		numeric		= NULL
				,@ve_cod_tipo_doc_pago		numeric		= NULL
				,@ve_cod_banco				numeric		= NULL
				,@ve_nro_doc				numeric		= NULL
				,@ve_fecha_doc				varchar(25)	= NULL
				,@ve_monto_doc				T_PRECIO	= NULL)

AS
BEGIN
	if (@ve_operacion='INSERT') 
		begin
			insert into doc_ingreso_pago(
				cod_ingreso_pago
				,cod_tipo_doc_pago
				,cod_banco
				,nro_doc
				,fecha_doc
				,monto_doc)
			values		(
				@ve_cod_ingreso_pago
				,@ve_cod_tipo_doc_pago
				,@ve_cod_banco
				,@ve_nro_doc
				,dbo.to_date(@ve_fecha_doc)
				,@ve_monto_doc) 
		end 

	else if (@ve_operacion='UPDATE') 
		begin
			update doc_ingreso_pago
			set cod_ingreso_pago	=	@ve_cod_ingreso_pago
				,cod_tipo_doc_pago	=	@ve_cod_tipo_doc_pago
				,cod_banco			=	@ve_cod_banco
				,nro_doc			=	@ve_nro_doc
				,fecha_doc			=	dbo.to_date(@ve_fecha_doc)
				,monto_doc			=	@ve_monto_doc
			where cod_doc_ingreso_pago = @ve_cod_doc_ingreso_pago
		end	
	else if (@ve_operacion='DELETE') 
		begin
			delete  doc_ingreso_pago
    		where cod_doc_ingreso_pago = @ve_cod_doc_ingreso_pago
		end 
END
go











