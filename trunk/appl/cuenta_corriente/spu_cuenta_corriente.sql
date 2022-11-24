-------------------- spu_cuenta_corriente ---------------------------------
alter PROCEDURE spu_cuenta_corriente(@ve_operacion varchar(20)
									, @ve_cod_cuenta_corriente numeric
									, @ve_nom_cuenta_corriente varchar(100)=NULL
									, @ve_nro_cuenta_corriente numeric=NULL
									, @ve_cod_cuenta_contable numeric=NULL
									, @ve_orden numeric=NULL )
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into cuenta_corriente 
			(nom_cuenta_corriente
			, nro_cuenta_corriente
			, cod_cuenta_contable
			, orden)
		values 
			(@ve_nom_cuenta_corriente
			, @ve_nro_cuenta_corriente
			, @ve_cod_cuenta_contable
			, @ve_orden)
	end 
	if (@ve_operacion='UPDATE') begin
		update cuenta_corriente 
		set nom_cuenta_corriente = @ve_nom_cuenta_corriente
			, nro_cuenta_corriente = @ve_nro_cuenta_corriente
			, cod_cuenta_contable = @ve_cod_cuenta_contable
			, orden = @ve_orden
		where cod_cuenta_corriente = @ve_cod_cuenta_corriente
	end
	else if (@ve_operacion='DELETE') begin
		delete cuenta_corriente 
    	where cod_cuenta_corriente = @ve_cod_cuenta_corriente
	end
		
	EXECUTE sp_orden_parametricas 'CUENTA_CORRIENTE'
END
go
