ALTER PROCEDURE [dbo].[spu_producto_local]( @ve_operacion varchar(20)
												,@ve_cod_producto_local numeric												
												,@ve_cod_producto varchar(30)=NULL
												,@ve_es_compuesto varchar(1)=NULL)
AS
BEGIN
	
	if (@ve_operacion='INSERT') begin
	insert into producto_local (cod_producto,
								es_compuesto) 
						values (@ve_cod_producto,
								@ve_es_compuesto)
	end 
	
		if (@ve_operacion='UPDATE') begin
		update producto_local
		set cod_producto = @ve_cod_producto,
			es_compuesto = @ve_es_compuesto
		where cod_producto = @ve_cod_producto
			
		end 
		else if (@ve_operacion='DELETE') begin
			delete producto_local 
			where cod_producto_local = @ve_cod_producto_local
		end 
	
END